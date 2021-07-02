<?php

    namespace Laravel\Nova;

    use Closure;
    use Illuminate\Database\Eloquent\Relations\Pivot;
    use Laravel\Nova\Actions\Actionable;
    use Laravel\Nova\Contracts\Cover;
    use Laravel\Nova\Contracts\Deletable;
    use Laravel\Nova\Contracts\ListableField;
    use Laravel\Nova\Contracts\RelatableField;
    use Laravel\Nova\Contracts\Resolvable;
    use Laravel\Nova\Fields\BelongsToMany;
    use Laravel\Nova\Fields\Downloadable;
    use Laravel\Nova\Fields\Field;
    use Laravel\Nova\Fields\FieldCollection;
    use Laravel\Nova\Fields\ID;
    use Laravel\Nova\Fields\MorphMany;
    use Laravel\Nova\Fields\MorphTo;
    use Laravel\Nova\Fields\MorphToMany;
    use Laravel\Nova\Http\Requests\NovaRequest;

    trait ResolvesFields
    {
        /**
         * Resolve the index fields.
         *
         * @param NovaRequest $request
         * @return FieldCollection
         */
        public function indexFields(NovaRequest $request)
        {
            return $this->availableFields($request)
                ->when($request->viaRelationship(), $this->fieldResolverCallback($request))
                ->filterForIndex($request, $this->resource)
                ->withoutListableFields()
                ->authorized($request)
                ->each(function ($field) use ($request) {
                    if ($field instanceof Resolvable && !$field->pivot) {
                        $field->resolveForDisplay($this->resource);
                    }

                    if ($field instanceof Resolvable && $field->pivot) {
                        $accessor = $this->pivotAccessorFor($request, $request->viaResource);

                        $field->resolveForDisplay($this->{$accessor} ?? new Pivot);
                    }
                });
        }

        /**
         * Get the fields that are available for the given request.
         *
         * @param NovaRequest $request
         * @return FieldCollection
         */
        public function availableFields(NovaRequest $request)
        {
            $method = $this->fieldsMethod($request);

            return FieldCollection::make(array_values($this->filter($this->{$method}($request))));
        }

        /**
         * Compute the method to use to get the available fields.
         *
         * @param NovaRequest $request
         * @return string
         */
        protected function fieldsMethod(NovaRequest $request)
        {
            if ($request->isResourceIndexRequest() && method_exists($this, 'fieldsForIndex')) {
                return 'fieldsForIndex';
            }

            if ($request->isResourceDetailRequest() && method_exists($this, 'fieldsForDetail')) {
                return 'fieldsForDetail';
            }

            if ($request->isCreateOrAttachRequest() && method_exists($this, 'fieldsForCreate')) {
                return 'fieldsForCreate';
            }

            if ($request->isUpdateOrUpdateAttachedRequest() && method_exists($this, 'fieldsForUpdate')) {
                return 'fieldsForUpdate';
            }

            return 'fields';
        }

        /**
         * Return the callback used for resolving fields.
         *
         * @param NovaRequest $request
         * @return Closure
         */
        protected function fieldResolverCallback(NovaRequest $request)
        {
            return function ($fields) use ($request) {
                $fields = $fields->values()->all();
                $pivotFields = $this->pivotFieldsFor($request, $request->viaResource)->all();

                if ($index = $this->indexToInsertPivotFields($request, $fields)) {
                    array_splice($fields, $index + 1, 0, $pivotFields);
                } else {
                    $fields = array_merge($fields, $pivotFields);
                }

                return FieldCollection::make($fields);
            };
        }

        /**
         * Get the pivot fields for the resource and relation.
         *
         * @param NovaRequest $request
         * @param string $relatedResource
         * @return FieldCollection
         */
        protected function pivotFieldsFor(NovaRequest $request, $relatedResource)
        {
            $fields = $this->availableFields($request)->filter(function ($field) use ($relatedResource) {
                return ($field instanceof BelongsToMany || $field instanceof MorphToMany) &&
                    isset($field->resourceName) && $field->resourceName == $relatedResource;
            });

            $field = $fields->count() === 1
                ? $fields->first(function ($field) {
                    return $field;
                }) : $fields->first(function ($field) use ($request) {
                    return $field->manyToManyRelationship === $request->viaRelationship;
                });

            if ($field && isset($field->fieldsCallback)) {
                return FieldCollection::make(array_values(
                    $this->filter(call_user_func($field->fieldsCallback, $request, $this->resource))
                ))->each(function ($field) {
                    $field->pivot = true;
                });
            }

            return FieldCollection::make();
        }

        /**
         * Get the index where the pivot fields should be spliced into the field array.
         *
         * @param NovaRequest $request
         * @param array $fields
         * @return int
         */
        protected function indexToInsertPivotFields(NovaRequest $request, array $fields)
        {
            foreach ($fields as $index => $field) {
                if (isset($field->resourceName) &&
                    $field->resourceName == $request->viaResource) {
                    return $index;
                }
            }
        }

        /**
         * Get the name of the pivot accessor for the requested relationship.
         *
         * @param NovaRequest $request
         * @param string $relatedResource
         * @return string
         */
        public function pivotAccessorFor(NovaRequest $request, $relatedResource)
        {
            $fields = $this->availableFields($request)->filter(function ($field) use ($relatedResource) {
                return ($field instanceof BelongsToMany || $field instanceof MorphToMany) &&
                    isset($field->resourceName) && $field->resourceName == $relatedResource;
            });

            $field = $fields->count() === 1
                ? $fields->first(function ($field) {
                    return $field;
                }) : $fields->first(function ($field) use ($request) {
                    return $field->manyToManyRelationship === $request->viaRelationship;
                });

            return $this->resource->{$field->manyToManyRelationship}()->getPivotAccessor();
        }

        /**
         * Resolve the deletable fields.
         *
         * @param NovaRequest $request
         * @return FieldCollection
         */
        public function deletableFields(NovaRequest $request)
        {
            $methods = collect(['fieldsForIndex', 'fieldsForDetail'])
                ->filter(function ($method) {
                    return method_exists($this, $method);
                })->all();

            return $this->buildAvailableFields($request, $methods)
                ->when($request->viaRelationship(), $this->fieldResolverCallback($request))
                ->whereInstanceOf(Deletable::class)
                ->unique(function ($field) {
                    return $field->attribute;
                })
                ->authorized($request)
                ->each(function ($field) use ($request) {
                    if (!$field instanceof Resolvable) {
                        return;
                    }

                    if ($field->pivot) {
                        $accessor = $this->pivotAccessorFor($request, $request->viaResource);

                        $field->resolveForDisplay($this->{$accessor} ?? new Pivot);
                    } else {
                        $field->resolveForDisplay($this->resource);
                    }
                });
        }

        /**
         * Get the fields that are available for the given request.
         *
         * @param NovaRequest $request
         * @param array $methods
         * @return FieldCollection
         */
        public function buildAvailableFields(NovaRequest $request, array $methods)
        {
            $fields = collect([
                method_exists($this, 'fields') ? $this->fields($request) : [],
            ]);

            $methods = collect($methods)
                ->filter(function ($method) {
                    return $method != 'fields';
                })->each(function ($method) use ($request, $fields) {
                    $fields->push([$this->{$method}($request)]);
                });

            return FieldCollection::make(array_values($this->filter($fields->flatten()->all())));
        }

        /**
         * Resolve the downloadable fields.
         *
         * @param NovaRequest $request
         * @return FieldCollection
         */
        public function downloadableFields(NovaRequest $request)
        {
            $methods = collect(['fieldsForIndex', 'fieldsForDetail'])
                ->filter(function ($method) {
                    return method_exists($this, $method);
                })->all();

            return $this->buildAvailableFields($request, $methods)
                ->when($request->viaRelationship(), $this->fieldResolverCallback($request))
                ->whereInstanceOf(Downloadable::class)
                ->unique(function ($field) {
                    return $field->attribute;
                })
                ->authorized($request)
                ->each(function ($field) use ($request) {
                    if (!$field instanceof Resolvable) {
                        return;
                    }

                    if ($field->pivot) {
                        $accessor = $this->pivotAccessorFor($request, $request->viaResource);

                        $field->resolveForDisplay($this->{$accessor} ?? new Pivot);
                    } else {
                        $field->resolveForDisplay($this->resource);
                    }
                });
        }

        /**
         * Determine resource has relatable field by attribute.
         *
         * @param NovaRequest $request
         * @param string $attribute
         * @return bool
         */
        public function hasRelatableField(NovaRequest $request, $attribute)
        {
            $methods = collect(['fieldsForIndex', 'fieldsForDetail'])
                ->filter(function ($method) {
                    return method_exists($this, $method);
                })->all();

            return $this->buildAvailableFields($request, $methods)
                    ->when($request->viaRelationship(), $this->fieldResolverCallback($request))
                    ->whereInstanceOf(RelatableField::class)
                    ->when($this->shouldAddActionsField($request), function ($fields) {
                        return $fields->push($this->actionfield());
                    })
                    ->first(function ($field) use ($attribute) {
                        return $field->attribute === $attribute;
                    }) !== null;
        }

        /**
         * Determine if the resource should have an Action field.
         *
         * @param NovaRequest $request
         * @return bool
         */
        protected function shouldAddActionsField($request)
        {
            return with($this->actionfield(), function ($actionField) use ($request) {
                return in_array(Actionable::class,
                        class_uses_recursive(static::newModel())) && $actionField->authorizedToSee($request);
            });
        }

        /**
         * Return a new Action field instance.
         *
         * @return MorphMany
         */
        protected function actionfield()
        {
            return MorphMany::make(__('Actions'), 'actions', Nova::actionResource())
                ->canSee(function ($request) {
                    return Nova::actionResource()::authorizedToViewAny($request);
                });
        }

        /**
         * Resolve the detail fields and assign them to their associated panel.
         *
         * @param NovaRequest $request
         * @param Resource $resource
         * @return FieldCollection
         */
        public function detailFieldsWithinPanels(NovaRequest $request, Resource $resource)
        {
            return $this->assignToPanels(
                Panel::defaultNameForDetail($resource ?? $request->newResource()),
                $this->detailFields($request)
            );
        }

        /**
         * Assign the fields with the given panels to their parent panel.
         *
         * @param string $label
         * @param FieldCollection $fields
         * @return FieldCollection
         */
        protected function assignToPanels($label, FieldCollection $fields)
        {
            return $fields->map(function ($field) use ($label) {
                if (!$field->panel) {
                    $field->panel = $label;
                }

                return $field;
            });
        }

        /**
         * Resolve the detail fields.
         *
         * @param NovaRequest $request
         * @return FieldCollection
         */
        public function detailFields(NovaRequest $request)
        {
            return $this->availableFields($request)
                ->when($request->viaRelationship(), $this->fieldResolverCallback($request))
                ->when($this->shouldAddActionsField($request), function ($fields) {
                    return $fields->push($this->actionfield());
                })
                ->filterForDetail($request, $this->resource)
                ->authorized($request)
                ->each(function ($field) use ($request) {
                    if ($field instanceof ListableField || !$field instanceof Resolvable) {
                        return;
                    }

                    if ($field->pivot) {
                        $accessor = $this->pivotAccessorFor($request, $request->viaResource);

                        $field->resolveForDisplay($this->{$accessor} ?? new Pivot);
                    } else {
                        $field->resolveForDisplay($this->resource);
                    }
                });
        }

        /**
         * Return the creation fields excluding any readonly ones.
         *
         * @param NovaRequest $request
         * @return FieldCollection
         */
        public function creationFieldsWithoutReadonly(NovaRequest $request)
        {
            return $this->creationFields($request)
                ->withoutReadonly($request);
        }

        /**
         * Resolve the creation fields.
         *
         * @param NovaRequest $request
         * @return FieldCollection
         */
        public function creationFields(NovaRequest $request)
        {
            $fields = $this->removeNonCreationFields(
                $request,
                $this->availableFields($request)->authorized($request)
            )->resolve($this->resource);

            return $request->viaRelationship()
                ? $this->withPivotFields($request, $fields->all())
                : $fields;
        }

        /**
         * Remove non-creation fields from the given collection.
         *
         * @param NovaRequest $request
         * @param FieldCollection $fields
         * @return FieldCollection
         */
        protected function removeNonCreationFields(NovaRequest $request, FieldCollection $fields)
        {
            return $fields->reject(function ($field) use ($request) {
                return $field instanceof ListableField ||
                    $field instanceof ResourceToolElement ||
                    $field->attribute === 'ComputedField' ||
                    ($field instanceof ID && $field->attribute === $this->resource->getKeyName()) ||
                    !$field->isShownOnCreation($request);
            });
        }

        /**
         * Merge the available pivot fields with the given fields.
         *
         * @param NovaRequest $request
         * @param array $fields
         * @return FieldCollection
         */
        protected function withPivotFields(NovaRequest $request, array $fields)
        {
            $pivotFields = $this->resolvePivotFields($request, $request->viaResource)->all();

            if ($index = $this->indexToInsertPivotFields($request, $fields)) {
                array_splice($fields, $index + 1, 0, $pivotFields);
            } else {
                $fields = array_merge($fields, $pivotFields);
            }

            return FieldCollection::make($fields);
        }

        /**
         * Resolve the pivot fields for the requested resource.
         *
         * @param NovaRequest $request
         * @param string $relatedResource
         * @return FieldCollection
         */
        public function resolvePivotFields(NovaRequest $request, $relatedResource)
        {
            $fields = $this->pivotFieldsFor($request, $relatedResource);

            return FieldCollection::make($this->filter($fields->each(function ($field) use (
                $request,
                $relatedResource
            ) {
                if ($field instanceof Resolvable) {
                    $accessor = $this->pivotAccessorFor($request, $relatedResource);

                    $field->resolve($this->{$accessor} ?? new Pivot);
                }
            })->authorized($request)->all()))->values();
        }

        /**
         * Resolve the creation fields and assign them to their associated panel.
         *
         * @param NovaRequest $request
         * @return FieldCollection
         */
        public function creationFieldsWithinPanels(NovaRequest $request)
        {
            return $this->assignToPanels(
                Panel::defaultNameForCreate($request->newResource()),
                $this->creationFields($request)
            );
        }

        /**
         * Resolve the creation pivot fields for a related resource.
         *
         * @param NovaRequest $request
         * @param string $relatedResource
         * @return FieldCollection
         */
        public function creationPivotFields(NovaRequest $request, $relatedResource)
        {
            return $this->removeNonCreationFields(
                $request, $this->resolvePivotFields($request, $relatedResource)
            );
        }

        /**
         * Return the update fields excluding any readonly ones.
         *
         * @param NovaRequest $request
         * @return FieldCollection
         */
        public function updateFieldsWithoutReadonly(NovaRequest $request)
        {
            return $this->updateFields($request)
                ->withoutReadonly($request);
        }

        /**
         * Resolve the update fields.
         *
         * @param NovaRequest $request
         * @return FieldCollection
         */
        public function updateFields(NovaRequest $request)
        {
            return $this->resolveFields($request, function ($fields) use ($request) {
                return $this->removeNonUpdateFields($request, $fields);
            });
        }

        /**
         * Resolve the given fields to their values.
         *
         * @param NovaRequest $request
         * @param Closure|null $filter
         * @return FieldCollection
         */
        protected function resolveFields(NovaRequest $request, Closure $filter = null)
        {
            $fields = $this->resolveNonPivotFields($request);

            if (!is_null($filter)) {
                $fields = $filter($fields);
            }

            return $request->viaRelationship()
                ? $this->withPivotFields($request, $fields->all())
                : $fields;
        }

        /**
         * Resolve the non pivot fields for the resource.
         *
         * @param NovaRequest $request
         * @return FieldCollection
         */
        protected function resolveNonPivotFields(NovaRequest $request)
        {
            return $this->availableFields($request)
                ->resolve($this->resource)
                ->authorized($request);
        }

        /**
         * Remove non-update fields from the given collection.
         *
         * @param NovaRequest $request
         * @param FieldCollection $fields
         * @return FieldCollection
         */
        protected function removeNonUpdateFields(NovaRequest $request, FieldCollection $fields)
        {
            return $fields->reject(function ($field) use ($request) {
                return $field instanceof ListableField ||
                    $field instanceof ResourceToolElement ||
                    $field->attribute === 'ComputedField' ||
                    ($field instanceof ID && $field->attribute === $this->resource->getKeyName()) ||
                    !$field->isShownOnUpdate($request, $this->resource);
            });
        }

        /**
         * Resolve the update fields and assign them to their associated panel.
         *
         * @param NovaRequest $request
         * @param Resource $resource
         * @return FieldCollection
         */
        public function updateFieldsWithinPanels(NovaRequest $request, Resource $resource = null)
        {
            return $this->assignToPanels(
                Panel::defaultNameForUpdate($resource ?? $request->newResource()),
                $this->updateFields($request)
            );
        }

        /**
         * Resolve the update pivot fields for a related resource.
         *
         * @param NovaRequest $request
         * @param string $relatedResource
         * @return FieldCollection
         */
        public function updatePivotFields(NovaRequest $request, $relatedResource)
        {
            return $this->removeNonUpdateFields(
                $request, $this->resolvePivotFields($request, $relatedResource)
            );
        }

        /**
         * Resolve the field for the given attribute.
         *
         * @param NovaRequest $request
         * @param string $attribute
         * @return Field
         */
        public function resolveFieldForAttribute(NovaRequest $request, $attribute)
        {
            return $this->resolveFields($request)->findFieldByAttribute($attribute);
        }

        /**
         * Resolve the inverse field for the given relationship attribute.
         *
         * This is primarily used for Relatable rule to check if has-one / morph-one relationships are "full".
         *
         * @param NovaRequest $request
         * @param string $attribute
         * @param string|null $morphType
         * @return FieldCollection
         */
        public function resolveInverseFieldsForAttribute(NovaRequest $request, $attribute, $morphType = null)
        {
            $field = $this->availableFields($request)
                ->authorized($request)
                ->findFieldByAttribute($attribute);

            if (!isset($field->resourceClass)) {
                return new FieldCollection;
            }

            $relatedResource = $field instanceof MorphTo
                ? Nova::resourceForKey($morphType ?? $request->{$attribute . '_type'})
                : ($field->resourceClass ?? null);

            $relatedResource = new $relatedResource($relatedResource::newModel());

            $result = $relatedResource->availableFields($request)->reject(function ($f) use ($field) {
                return isset($f->attribute) &&
                    isset($field->inverse) &&
                    $f->attribute !== $field->inverse;
            })->filter(function ($field) use ($request) {
                return isset($field->resourceClass) &&
                    $field->resourceClass == $request->resource();
            });

            return $result;
        }

        /**
         * Resolve the resource's avatar URL, if applicable.
         *
         * @param NovaRequest $request
         * @return string|null
         */
        public function resolveAvatarUrl(NovaRequest $request)
        {
            $field = $this->resolveAvatarField($request);

            if ($field) {
                return $field->resolveThumbnailUrl();
            }
        }

        /**
         * Resolve the resource's avatar field.
         *
         * @param NovaRequest $request
         * @return Cover|null
         */
        public function resolveAvatarField(NovaRequest $request)
        {
            return tap($this->availableFields($request)
                ->authorized($request)
                ->whereInstanceOf(Cover::class)
                ->first(),
                function ($field) {
                    if ($field instanceof Resolvable) {
                        $field->resolve($this->resource);
                    }
                }
            );
        }

        /**
         * Determine whether the resource's avatar should be rounded, if applicable.
         *
         * @param NovaRequest $request
         * @return bool
         */
        public function resolveIfAvatarShouldBeRounded(NovaRequest $request)
        {
            $field = $this->resolveAvatarField($request);

            if ($field) {
                return $field->isRounded();
            }

            return false;
        }

        /**
         * Get the panels that are available for the given create request.
         *
         * @param NovaRequest $request
         * @return array
         */
        public function availablePanelsForCreate($request)
        {
            return $this->panelsWithDefaultLabel(Panel::defaultNameForCreate($request->newResource()), $request);
        }

        /**
         * Return the panels for this request with the default label.
         *
         * @param string $label
         * @param NovaRequest $request
         * @return array
         */
        protected function panelsWithDefaultLabel($label, NovaRequest $request)
        {
            $method = $this->fieldsMethod($request);

            return with(
                collect(array_values($this->{$method}($request)))->whereInstanceOf(Panel::class)->values(),
                function ($panels) use ($label) {
                    return $panels->when($panels->where('name', $label)->isEmpty(), function ($panels) use ($label) {
                        return $panels->prepend((new Panel($label))->withToolbar());
                    })->all();
                }
            );
        }

        /**
         * Get the panels that are available for the given update request.
         *
         * @param NovaRequest $request
         * @param Resource $resource
         * @return array
         */
        public function availablePanelsForUpdate(NovaRequest $request, Resource $resource = null)
        {
            return $this->panelsWithDefaultLabel(Panel::defaultNameForUpdate($resource ?? $request->newResource()),
                $request);
        }

        /**
         * Get the panels that are available for the given detail request.
         *
         * @param NovaRequest $request
         * @param Resource $resource
         * @return array
         */
        public function availablePanelsForDetail(NovaRequest $request, Resource $resource)
        {
            return $this->panelsWithDefaultLabel(Panel::defaultNameForDetail($resource ?? $request->newResource()),
                $request);
        }

        /**
         * Get the displayable pivot model name from a field.
         *
         * @param NovaRequest $request
         * @param string $field
         * @return string|null
         */
        public function pivotNameForField(NovaRequest $request, $field)
        {
            $field = $this->availableFields($request)->findFieldByAttribute($field);

            if (!$field || (!$field instanceof BelongsToMany &&
                    !$field instanceof MorphToMany)) {
                return self::DEFAULT_PIVOT_NAME;
            }

            if (isset($field->pivotName)) {
                return $field->pivotName;
            }
        }

        /**
         * Resolve the detail fields for the resource.
         *
         * @param NovaRequest $request
         * @param Closure $filter
         * @return FieldCollection
         */
        protected function resolveFieldsForDetail(NovaRequest $request, Closure $filter)
        {
            $fields = $this->resolveNonPivotFields($request);

            return $request->viaRelationship()
                ? $this->withPivotFields($request, $fields->all())
                : $fields;
        }
    }
