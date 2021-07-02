<?php

    namespace Laravel\Nova;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\Pivot;
    use Illuminate\Support\Collection;
    use Laravel\Nova\Http\Requests\NovaRequest;

    trait FillsFields
    {
        /**
         * Fill a new model instance using the given request.
         *
         * @param NovaRequest $request
         * @param Model $model
         * @return array
         */
        public static function fill(NovaRequest $request, $model)
        {
            return static::fillFields(
                $request, $model,
                (new static($model))->creationFieldsWithoutReadonly($request)
            );
        }

        /**
         * Fill the given fields for the model.
         *
         * @param NovaRequest $request
         * @param Model $model
         * @param Collection $fields
         * @return array
         */
        protected static function fillFields(NovaRequest $request, $model, $fields)
        {
            return [
                $model,
                $fields->map->fill($request, $model)->filter(function ($callback) {
                    return is_callable($callback);
                })->values()->all()
            ];
        }

        /**
         * Fill a new model instance using the given request.
         *
         * @param NovaRequest $request
         * @param Model $model
         * @return array
         */
        public static function fillForUpdate(NovaRequest $request, $model)
        {
            return static::fillFields(
                $request, $model,
                (new static($model))->updateFieldsWithoutReadonly($request)
            );
        }

        /**
         * Fill a new pivot model instance using the given request.
         *
         * @param NovaRequest $request
         * @param Model $model
         * @param Pivot $pivot
         * @return array
         */
        public static function fillPivot(NovaRequest $request, $model, $pivot)
        {
            $instance = new static($model);

            return static::fillFields(
                $request, $pivot,
                $instance->creationPivotFields($request, $request->relatedResource)
            );
        }

        /**
         * Fill a new pivot model instance using the given request.
         *
         * @param NovaRequest $request
         * @param Model $model
         * @param Pivot $pivot
         * @return array
         */
        public static function fillPivotForUpdate(NovaRequest $request, $model, $pivot)
        {
            $instance = new static($model);

            return static::fillFields(
                $request, $pivot,
                $instance->updatePivotFields($request, $request->relatedResource)
            );
        }
    }
