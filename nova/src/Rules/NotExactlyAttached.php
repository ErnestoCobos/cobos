<?php

    namespace Laravel\Nova\Rules;

    use Illuminate\Contracts\Validation\Rule;
    use Illuminate\Database\Eloquent\Model;
    use Laravel\Nova\Http\Requests\NovaRequest;
    use Laravel\Nova\Nova;

    class NotExactlyAttached implements Rule
    {
        /**
         * The request instance.
         *
         * @var NovaRequest
         */
        public $request;

        /**
         * The model instance.
         *
         * @var Model
         */
        public $model;

        /**
         * Create a new rule instance.
         *
         * @param NovaRequest $request
         * @param Model $model
         * @return void
         */
        public function __construct(NovaRequest $request, $model)
        {
            $this->model = $model;
            $this->request = $request;
        }

        /**
         * Determine if the validation rule passes.
         *
         * @param string $attribute
         * @param mixed $value
         * @return bool
         */
        public function passes($attribute, $value)
        {
            $query = $this->model
                ->{$this->request->viaRelationship}()
                ->withoutGlobalScopes();

            $relatedModel = $query->getModel();

            $resource = with(Nova::resourceForModel($this->model), function ($resource) {
                return new $resource($this->model);
            });

            $resource->resolvePivotFields($this->request, $this->request->relatedResource)
                ->each(function ($field) use ($query) {
                    $query->wherePivot($field->attribute, $this->request->input($field->attribute));
                });

            return !in_array(
                $this->request->input($this->request->relatedResource),
                $query->pluck($relatedModel->getQualifiedKeyName())->all()
            );
        }

        /**
         * Get the validation error message.
         *
         * @return string
         */
        public function message()
        {
            return trans('nova::validation.attached');
        }
    }
