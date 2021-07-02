<?php

    namespace Laravel\Nova\Http\Requests;

    use Illuminate\Database\Eloquent\Builder;
    use Illuminate\Database\Eloquent\Model;
    use Laravel\Nova\Nova;
    use Laravel\Nova\Resource;

    trait InteractsWithResources
    {
        /**
         * Determine if the user is authorized to make this request.
         *
         * @return bool
         */
        public function authorize()
        {
            return true;
        }

        /**
         * Get the validation rules that apply to the request.
         *
         * @return array
         */
        public function rules()
        {
            return [
                //
            ];
        }

        /**
         * Determine if the requested resource is soft deleting.
         *
         * @return bool
         */
        public function resourceSoftDeletes()
        {
            $resource = $this->resource();

            return $resource::softDeletes();
        }

        /**
         * Get the class name of the resource being requested.
         *
         * @return mixed
         */
        public function resource()
        {
            return tap(Nova::resourceForKey($this->route('resource')), function ($resource) {
                abort_if(is_null($resource), 404);
                abort_if(!$resource::authorizedToViewAny($this), 403);
            });
        }

        /**
         * Get a new instance of the resource being requested.
         *
         * @return Resource
         */
        public function newResource()
        {
            $resource = $this->resource();

            return new $resource($this->model());
        }

        /**
         * Get a new instance of the underlying model.
         *
         * @return Model
         */
        public function model()
        {
            $resource = $this->resource();

            return $resource::newModel();
        }

        /**
         * Find the resource model instance for the request.
         *
         * @param mixed|null $resourceId
         * @return Resource
         */
        public function findResourceOrFail($resourceId = null)
        {
            return $this->newResourceWith($this->findModelOrFail($resourceId));
        }

        /**
         * Get a new instance of the resource being requested.
         *
         * @param Model $model
         * @return Resource
         */
        public function newResourceWith($model)
        {
            $resource = $this->resource();

            return new $resource($model);
        }

        /**
         * Find the model instance for the request.
         *
         * @param mixed|null $resourceId
         * @return Model
         */
        public function findModelOrFail($resourceId = null)
        {
            if ($resourceId) {
                return $this->findModelQuery($resourceId)->firstOrFail();
            }

            return once(function () {
                return $this->findModelQuery()->firstOrFail();
            });
        }

        /**
         * Get the query to find the model instance for the request.
         *
         * @param mixed|null $resourceId
         * @return Builder
         */
        public function findModelQuery($resourceId = null)
        {
            return $this->newQueryWithoutScopes()->whereKey(
                $resourceId ?? $this->resourceId
            );
        }

        /**
         * Get a new, scopeless query builder for the underlying model.
         *
         * @return Builder
         */
        public function newQueryWithoutScopes()
        {
            return $this->model()->newQueryWithoutScopes();
        }

        /**
         * Get a new query builder for the underlying model.
         *
         * @return Builder
         */
        public function newQuery()
        {
            return $this->model()->newQuery();
        }
    }
