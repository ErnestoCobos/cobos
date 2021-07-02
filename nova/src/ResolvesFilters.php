<?php

    namespace Laravel\Nova;

    use Illuminate\Http\Request;
    use Illuminate\Support\Collection;
    use Laravel\Nova\Http\Requests\NovaRequest;

    trait ResolvesFilters
    {
        /**
         * Get the filters that are available for the given request.
         *
         * @param NovaRequest $request
         * @return Collection
         */
        public function availableFilters(NovaRequest $request)
        {
            return $this->resolveFilters($request)->filter->authorizedToSee($request)->values();
        }

        /**
         * Get the filters for the given request.
         *
         * @param NovaRequest $request
         * @return Collection
         */
        public function resolveFilters(NovaRequest $request)
        {
            return collect(array_values($this->filter($this->filters($request))));
        }

        /**
         * Get the filters available on the entity.
         *
         * @param Request $request
         * @return array
         */
        public function filters(Request $request)
        {
            return [];
        }
    }
