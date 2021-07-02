<?php

    namespace Laravel\Nova\Query;

    use Closure;
    use Illuminate\Container\Container;
    use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
    use Illuminate\Database\Eloquent\Collection;
    use Illuminate\Pagination\Paginator;
    use Illuminate\Support\LazyCollection;
    use Laravel\Nova\Contracts\QueryBuilder;
    use Laravel\Nova\Http\Requests\NovaRequest;
    use Laravel\Nova\TrashedStatus;
    use Laravel\Scout\Builder as ScoutBuilder;
    use RuntimeException;

    class Builder implements QueryBuilder
    {
        /**
         * The resource class.
         *
         * @var string
         */
        protected $resource;

        /**
         * The original query builder instance.
         *
         * @var EloquentBuilder
         */
        protected $originalQueryBuilder;

        /**
         * The query builder instance.
         *
         * @var ScoutBuilder|EloquentBuilder
         */
        protected $queryBuilder;

        /**
         * Optional callbacks before model query execution.
         *
         * @var array
         */
        protected $queryCallbacks = [];

        /**
         * Determine query callbacks has been applied.
         *
         * @var bool
         */
        protected $appliedQueryCallbacks = false;

        /**
         * Construct a new query builder for a resource.
         *
         * @param string $resource
         * @return void
         */
        public function __construct($resource)
        {
            $this->resource = $resource;
        }

        /**
         * Build a "whereKey" query for the given resource.
         *
         * @param EloquentBuilder $query
         * @param string $key
         */
        public function whereKey($query, $key)
        {
            $this->setOriginalQueryBuilder($this->queryBuilder = $query);

            $this->tap(function ($query) use ($key) {
                $query->whereKey($key);
            });
        }

        /**
         * Set original query builder instance.
         *
         * @param EloquentBuilder $queryBuilder
         * @return void
         */
        protected function setOriginalQueryBuilder($queryBuilder)
        {
            if (isset($this->originalQueryBuilder)) {
                throw new RuntimeException('Unable to override $originalQueryBuilder, please create a new ' . self::class);
            }

            $this->originalQueryBuilder = $queryBuilder;
        }

        /**
         * Pass the query to a given callback.
         *
         * @param Closure $callback
         * @return $this
         */
        public function tap($callback)
        {
            $this->queryCallbacks[] = $callback;

            return $this;
        }

        /**
         * Build a "search" query for the given resource.
         *
         * @param NovaRequest $request
         * @param EloquentBuilder $query
         * @param string|null $search
         * @param array $filters
         * @param array $orderings
         * @param string $withTrashed
         * @return $this
         */
        public function search(
            NovaRequest $request,
            $query,
            $search = null,
            array $filters = [],
            array $orderings = [],
            $withTrashed = TrashedStatus::DEFAULT
        ) {
            $this->setOriginalQueryBuilder($query);

            $hasSearchKeyword = !empty(trim($search));
            $hasOrderings = collect($orderings)->filter()->isNotEmpty();

            if ($this->resource::usesScout()) {
                if ($hasSearchKeyword) {
                    $this->queryBuilder = $this->resource::buildIndexQueryUsingScout($request, $search, $withTrashed);
                    $search = '';
                }

                if (!$hasSearchKeyword && !$hasOrderings) {
                    $this->tap(function ($query) {
                        $query->latest($query->getModel()->getQualifiedKeyName());
                    });
                }
            }

            if (!isset($this->queryBuilder)) {
                $this->queryBuilder = $query;
            }

            $this->tap(function ($query) use ($request, $search, $filters, $orderings, $withTrashed) {
                $this->resource::buildIndexQuery(
                    $request, $query, $search, $filters, $orderings, $withTrashed
                );
            });

            return $this;
        }

        /**
         * Set the "take" for the search query.
         *
         * @param int $limit
         * @return $this
         */
        public function take($limit)
        {
            return $this->limit($limit);
        }

        /**
         * Set the "limit" for the search query.
         *
         * @param int $limit
         * @return $this
         */
        public function limit($limit)
        {
            if ($this->queryBuilder instanceof EloquentBuilder) {
                $this->queryBuilder->limit($limit);
            } else {
                $this->queryBuilder->take($limit);
            }

            return $this;
        }

        /**
         * Get the results of the search.
         *
         * @return Collection
         */
        public function get()
        {
            return $this->applyQueryCallbacks($this->queryBuilder)->get();
        }

        /**
         * Apply any query callbacks to the query builder.
         *
         * @param ScoutBuilder|EloquentBuilder $queryBuilder
         * @return ScoutBuilder|EloquentBuilder
         */
        protected function applyQueryCallbacks($queryBuilder)
        {
            if ($this->appliedQueryCallbacks === true) {
                return $queryBuilder;
            }

            $callback = function ($queryBuilder) {
                collect($this->queryCallbacks)
                    ->filter()
                    ->each(function ($callback) use ($queryBuilder) {
                        call_user_func($callback, $queryBuilder);
                    });
            };

            if ($queryBuilder instanceof ScoutBuilder) {
                $queryBuilder->query($callback);
            } else {
                $queryBuilder->tap($callback);
            }

            $this->appliedQueryCallbacks = true;

            return $queryBuilder;
        }

        /**
         * Get a lazy collection for the given query.
         *
         * @return LazyCollection
         */
        public function cursor()
        {
            $queryBuilder = $this->applyQueryCallbacks($this->queryBuilder);

            if (method_exists($queryBuilder, 'cursor')) {
                return $queryBuilder->cursor();
            }

            return LazyCollection::make(function () use ($queryBuilder) {
                yield from $queryBuilder->get()
                    ->each(function ($result) {
                        yield $result;
                    });
            });
        }

        /**
         * Get the paginated results of the query.
         *
         * @param int $perPage
         * @return array
         */
        public function paginate($perPage)
        {
            $queryBuilder = $this->applyQueryCallbacks($this->queryBuilder);

            if ($queryBuilder instanceof EloquentBuilder) {
                return [
                    $queryBuilder->simplePaginate($perPage),
                    $this->getCountForPagination(),
                ];
            }

            $scoutPaginated = $queryBuilder->paginate($perPage);

            $items = $scoutPaginated->items();

            $hasMorePages = ($scoutPaginated->perPage() * $scoutPaginated->currentPage()) < $scoutPaginated->total();

            return [
                Container::getInstance()->makeWith(Paginator::class, [
                    'items' => $items,
                    'perPage' => $scoutPaginated->perPage(),
                    'currentPage' => $scoutPaginated->currentPage(),
                    'options' => $scoutPaginated->getOptions(),
                ])->hasMorePagesWhen($hasMorePages),
                $scoutPaginated->total(),
            ];
        }

        /**
         * Get the count of the total records for the paginator.
         *
         * @return int|null
         */
        public function getCountForPagination()
        {
            return $this->toBaseQueryBuilder()->getCountForPagination();
        }

        /**
         * Convert the query builder to an fluent query builder (skip using Scout).
         *
         * @return \Illuminate\Database\Query\Builder
         */
        public function toBaseQueryBuilder()
        {
            return $this->toBase()->toBase();
        }

        /**
         * Convert the query builder to an Eloquent query builder (skip using Scout).
         *
         * @return EloquentBuilder
         */
        public function toBase()
        {
            return $this->applyQueryCallbacks($this->originalQueryBuilder);
        }
    }
