<?php

    namespace Laravel\Nova\Http\Controllers;

    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Response;
    use Illuminate\Routing\Controller;
    use Laravel\Nova\Http\Requests\LensMetricRequest;

    class LensMetricController extends Controller
    {
        /**
         * List the metrics for the given resource.
         *
         * @param LensMetricRequest $request
         * @return JsonResponse
         */
        public function index(LensMetricRequest $request)
        {
            return response()->json(
                $request->availableMetrics()
            );
        }

        /**
         * Get the specified metric's value.
         *
         * @param LensMetricRequest $request
         * @return Response
         */
        public function show(LensMetricRequest $request)
        {
            return response()->json([
                'value' => $request->metric()->resolve($request),
            ]);
        }
    }
