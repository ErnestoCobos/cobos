<?php

    namespace Laravel\Nova\Http\Controllers;

    use Illuminate\Http\Response;
    use Illuminate\Routing\Controller;
    use Laravel\Nova\Http\Requests\NovaRequest;

    class CreationFieldController extends Controller
    {
        /**
         * List the creation fields for the given resource.
         *
         * @param NovaRequest $request
         * @return Response
         */
        public function index(NovaRequest $request)
        {
            $resourceClass = $request->resource();

            $resourceClass::authorizeToCreate($request);

            return response()->json([
                'fields' => $request->newResource()->creationFieldsWithinPanels($request),
                'panels' => $request->newResource()->availablePanelsForCreate($request),
            ]);
        }
    }
