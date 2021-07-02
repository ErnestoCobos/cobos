<?php

    namespace Laravel\Nova\Http\Controllers;

    use DateTime;
    use Exception;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
    use Illuminate\Database\Eloquent\Relations\Pivot;
    use Illuminate\Http\Response;
    use Illuminate\Routing\Controller;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Validator;
    use Laravel\Nova\Http\Requests\NovaRequest;
    use Laravel\Nova\Nova;

    class ResourceAttachController extends Controller
    {
        use HandlesCustomRelationKeys;

        /**
         * Attach a related resource to the given resource.
         *
         * @param NovaRequest $request
         * @return Response
         */
        public function handle(NovaRequest $request)
        {
            $resource = $request->resource();

            $model = $request->findModelOrFail();

            tap(new $resource($model), function ($resource) use ($request) {
                abort_unless($resource->hasRelatableField($request, $request->viaRelationship), 404);
            });

            $this->validate($request, $model, $resource);

            DB::transaction(function () use ($request, $resource, $model) {
                [$pivot, $callbacks] = $resource::fillPivot(
                    $request, $model, $this->initializePivot(
                    $request, $model->{$request->viaRelationship}()
                )
                );

                Nova::actionEvent()->forAttachedResource($request, $model, $pivot)->save();

                $pivot->save();

                collect($callbacks)->each->__invoke();
            });
        }

        /**
         * Validate the attachment request.
         *
         * @param NovaRequest $request
         * @param Model $model
         * @param string $resource
         * @return void
         */
        protected function validate(NovaRequest $request, $model, $resource)
        {
            $attribute = $resource::validationAttachableAttributeFor($request, $request->relatedResource);

            tap($this->creationRules($request, $resource), function ($rules) use ($resource, $request, $attribute) {
                Validator::make($request->all(), $rules, [], $this->customRulesKeys($request, $attribute))->validate();

                $resource::validateForAttachment($request);
            });
        }

        /**
         * Return the validation rules used for the request. Correctly aasign the rules used
         * to the main attribute if the user has defined a custom relation key.
         *
         * @param NovaRequest $request
         * @param string $resource
         * @return mixed
         */
        protected function creationRules(NovaRequest $request, $resource)
        {
            $rules = $resource::creationRulesFor($request, $this->getRuleKey($request));

            if ($this->usingCustomRelationKey($request)) {
                $rules[$request->relatedResource] = $rules[$request->viaRelationship];
                unset($rules[$request->viaRelationship]);
            }

            return $rules;
        }

        /**
         * Initialize a fresh pivot model for the relationship.
         *
         * @param NovaRequest $request
         * @param BelongsToMany $relationship
         * @return Pivot
         * @throws Exception
         */
        protected function initializePivot(NovaRequest $request, $relationship)
        {
            $parentKey = $request->resourceId;
            $relatedKey = $request->input($request->relatedResource);

            $parentKeyName = $relationship->getParentKeyName();
            $relatedKeyName = $relationship->getRelatedKeyName();

            if ($parentKeyName !== $request->model()->getKeyName()) {
                $parentKey = $request->findModelOrFail()->{$parentKeyName};
            }

            if ($relatedKeyName !== ($request->newRelatedResource()::newModel())->getKeyName()) {
                $relatedKey = $request->findRelatedModelOrFail()->{$relatedKeyName};
            }

            ($pivot = $relationship->newPivot())->forceFill([
                $relationship->getForeignPivotKeyName() => $parentKey,
                $relationship->getRelatedPivotKeyName() => $relatedKey,
            ]);

            if ($relationship->withTimestamps) {
                $pivot->forceFill([
                    $relationship->createdAt() => new DateTime,
                    $relationship->updatedAt() => new DateTime,
                ]);
            }

            return $pivot;
        }
    }
