<?php

    namespace Laravel\Nova;

    use Closure;
    use Illuminate\Http\Request;

    trait AuthorizesRequests
    {
        /**
         * The callback that should be used to authenticate Nova users.
         *
         * @var Closure
         */
        public static $authUsing;

        /**
         * Register the Nova authentication callback.
         *
         * @param Closure $callback
         * @return static
         */
        public static function auth($callback)
        {
            static::$authUsing = $callback;

            return new static;
        }

        /**
         * Determine if the given request can access the Nova dashboard.
         *
         * @param Request $request
         * @return bool
         */
        public static function check($request)
        {
            return (static::$authUsing ?: function () {
                return app()->environment('local');
            })($request);
        }
    }
