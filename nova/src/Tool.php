<?php

    namespace Laravel\Nova;

    use Illuminate\Http\Request;
    use Illuminate\View\View;

    abstract class Tool
    {
        use AuthorizedToSee,
            Makeable,
            ProxiesCanSeeToGate;

        /**
         * Create a new Tool.
         *
         * @return void
         */
        public function __construct()
        {
            //
        }

        /**
         * Determine if the element should be displayed for the given request.
         *
         * @param Request $request
         * @return bool
         */
        public function authorize(Request $request)
        {
            return $this->authorizedToSee($request);
        }

        /**
         * Perform any tasks that need to happen on tool registration.
         *
         * @return void
         */
        public function boot()
        {
            //
        }

        /**
         * Build the view that renders the navigation links for the tool.
         *
         * @return View|string
         */
        public function renderNavigation()
        {
            return '';
        }
    }
