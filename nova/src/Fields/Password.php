<?php

    namespace Laravel\Nova\Fields;

    use Illuminate\Support\Facades\Hash;
    use Laravel\Nova\Http\Requests\NovaRequest;

    class Password extends Field
    {
        /**
         * The field's component.
         *
         * @var string
         */
        public $component = 'password-field';

        /**
         * Prepare the field for JSON serialization.
         *
         * @return array
         */
        public function jsonSerialize()
        {
            return array_merge(
                parent::jsonSerialize(),
                ['value' => '']
            );
        }

        /**
         * Hydrate the given attribute on the model based on the incoming request.
         *
         * @param NovaRequest $request
         * @param string $requestAttribute
         * @param object $model
         * @param string $attribute
         * @return mixed
         */
        protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
        {
            if (!empty($request[$requestAttribute])) {
                $model->{$attribute} = Hash::make($request[$requestAttribute]);
            }
        }
    }
