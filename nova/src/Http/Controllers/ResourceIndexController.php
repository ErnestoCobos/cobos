<?php

    namespace Laravel\Nova\Http\Controllers;

    use Illuminate\Http\JsonResponse;
    use Illuminate\Routing\Controller;
    use Laravel\Nova\Http\Requests\ResourceIndexRequest;

    class ResourceIndexController extends Controller
    {
        /**
         * List the resources for administration.
         *
         * @param ResourceIndexRequest $request
         * @return JsonResponse
         */
        public function handle(ResourceIndexRequest $request)
        {
            $resource = $request->resource();

            [$paginator, $total] = $request->searchIndex();

            return response()->json([
                'label' => $resource::label(),
                'resources' => $paginator->getCollection()->mapInto($resource)->map->serializeForIndex($request),
                'prev_page_url' => $paginator->previousPageUrl(),
                'next_page_url' => $paginator->nextPageUrl(),
                'per_page' => $paginator->perPage(),
                'per_page_options' => $resource::perPageOptions(),
                'total' => $total,
                'softDeletes' => $resource::softDeletes(),
            ]);
        }
    }
