<?php

    namespace Laravel\Nova\Http\Controllers;

    use Illuminate\Http\JsonResponse;
    use Illuminate\Routing\Controller;
    use Laravel\Nova\Http\Requests\LensCardRequest;

    class LensCardController extends Controller
    {
        /**
         * List the cards for the given lens.
         *
         * @param LensCardRequest $request
         * @return JsonResponse
         */
        public function index(LensCardRequest $request)
        {
            return response()->json(
                $request->availableCards()
            );
        }
    }
