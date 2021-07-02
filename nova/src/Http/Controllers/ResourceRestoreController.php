<?php

    namespace Laravel\Nova\Http\Controllers;

    use Illuminate\Http\Response;
    use Illuminate\Routing\Controller;
    use Illuminate\Support\Facades\DB;
    use Laravel\Nova\Http\Requests\RestoreResourceRequest;
    use Laravel\Nova\Nova;

    class ResourceRestoreController extends Controller
    {
        /**
         * Restore the given resource(s).
         *
         * @param RestoreResourceRequest $request
         * @return Response
         */
        public function handle(RestoreResourceRequest $request)
        {
            $request->chunks(150, function ($models) use ($request) {
                $models->each(function ($model) use ($request) {
                    $model->restore();

                    tap(Nova::actionEvent(), function ($actionEvent) use ($model, $request) {
                        DB::connection($actionEvent->getConnectionName())->table('action_events')->insert(
                            $actionEvent->forResourceRestore($request->user(), collect([$model]))
                                ->map->getAttributes()->all()
                        );
                    });
                });
            });
        }
    }
