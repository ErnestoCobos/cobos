<?php

    namespace Laravel\Nova\Events;

    use Illuminate\Foundation\Events\Dispatchable;
    use Illuminate\Http\Request;

    class ServingNova
    {
        use Dispatchable;

        /**
         * The request instance.
         *
         * @var Request
         */
        public $request;

        /**
         * Create a new event instance.
         *
         * @param Request $request
         * @return void
         */
        public function __construct(Request $request)
        {
            $this->request = $request;
        }
    }
