<?php

    namespace Laravel\Nova\Http\Controllers;

    use DateTime;
    use Illuminate\Http\Response;
    use Illuminate\Routing\Controller;
    use Illuminate\Support\Arr;
    use Laravel\Nova\Http\Requests\NovaRequest;
    use Laravel\Nova\Nova;
    use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

    class StyleController extends Controller
    {
        /**
         * Serve the requested stylesheet.
         *
         * @param NovaRequest $request
         * @return Response
         *
         * @throws NotFoundHttpException
         */
        public function show(NovaRequest $request)
        {
            $path = Arr::get(Nova::allStyles(), $request->style);

            abort_if(is_null($path), 404);

            return response(
                file_get_contents($path),
                200,
                [
                    'Content-Type' => 'text/css',
                ]
            )->setLastModified(DateTime::createFromFormat('U', (string)filemtime($path)));
        }
    }
