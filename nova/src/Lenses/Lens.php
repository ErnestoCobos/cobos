<?php

    namespace Laravel\Nova\Lenses;

    use ArrayAccess;
    use Illuminate\Contracts\Routing\UrlRoutable;
    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\ConditionallyLoadsAttributes;
    use Illuminate\Http\Resources\DelegatesToResource;
    use Illuminate\Support\Str;
    use JsonSerializable;
    use Laravel\Nova\AuthorizedToSee;
    use Laravel\Nova\Contracts\ListableField;
    use Laravel\Nova\Fields\FieldCollection;
    use Laravel\Nova\Fields\ID;
    use Laravel\Nova\Http\Requests\LensRequest;
    use Laravel\Nova\Http\Requests\NovaRequest;
    use Laravel\Nova\Makeable;
    use Laravel\Nova\Nova;
    use Laravel\Nova\ProxiesCanSeeToGate;
    use Laravel\Nova\ResolvesActions;
    use Laravel\Nova\ResolvesCards;
    use Laravel\Nova\ResolvesFilters;
    use stdClass;

    abstract class Lens implements ArrayAccess, JsonSerializable, UrlRoutable
    {
        use
            AuthorizedToSee,
            ConditionallyLoadsAttributes,
            DelegatesToResource,
            Makeable,
            ProxiesCanSeeToGate,
            ResolvesActions,
            ResolvesCards,
            ResolvesFilters;

        /**
         * The displayable name of the lens.
         *
         * @var string
         */
        public $name;

        /**
         * The underlying model resource instance.
         *
         * @var Model|stdClass
         */
        public $resource;

        /**
         * Create a new lens instance.
         *
         * @param Model|null $resource
         * @return void
         */
        public function __construct($resource = null)
        {
            $this->resource = $resource ?: new stdClass;
        }

        /**
         * Execute the query for the lens.
         *
         * @param LensRequest $request
         * @param Builder $query
         * @return mixed
         */
        abstract public static function query(LensRequest $request, $query);

        /**
         * Get the actions available on the lens.
         *
         * @param Request $request
         * @return array
         */
        public function actions(Request $request)
        {
            return $request->newResource()->actions($request);
        }

        /**
         * Prepare the resource for JSON serialization.
         *
         * @param NovaRequest $request
         * @return array
         */
        public function serializeForIndex(NovaRequest $request)
        {
            return $this->serializeWithId($this->resolveFields($request)
                ->reject(function ($field) {
                    return $field instanceof ListableField || !$field->showOnIndex;
                }));
        }

        /**
         * Prepare the lens for JSON serialization using the given fields.
         *
         * @param FieldCollection $fields
         * @return array
         */
        protected function serializeWithId(FieldCollection $fields)
        {
            return [
                'id' => $fields->whereInstanceOf(ID::class)->first() ?: ID::forModel($this->resource),
                'fields' => $fields->all(),
            ];
        }

        /**
         * Resolve the given fields to their values.
         *
         * @param NovaRequest $request
         * @return FieldCollection
         */
        public function resolveFields(NovaRequest $request)
        {
            return $this->availableFields($request)
                ->resolve($this->resource)
                ->authorized($request)
                ->resolveForDisplay($this->resource);
        }

        /**
         * Get the fields that are available for the given request.
         *
         * @param NovaRequest $request
         * @return FieldCollection
         */
        public function availableFields(NovaRequest $request)
        {
            return new FieldCollection(array_values($this->filter($this->fields($request))));
        }

        /**
         * Get the fields displayed by the lens.
         *
         * @param Request $request
         * @return array
         */
        abstract public function fields(Request $request);

        /**
         * Prepare the lens for JSON serialization.
         *
         * @return array
         */
        public function jsonSerialize()
        {
            return [
                'name' => $this->name(),
                'uriKey' => $this->uriKey(),
            ];
        }

        /**
         * Get the displayable name of the lens.
         *
         * @return string
         */
        public function name()
        {
            return $this->name ?: Nova::humanize($this);
        }

        /**
         * Get the URI key for the lens.
         *
         * @return string
         */
        public function uriKey()
        {
            return Str::slug($this->name(), '-', null);
        }
    }
