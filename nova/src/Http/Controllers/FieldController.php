<?php

    namespace Laravel\Nova\Http\Controllers;

    use Illuminate\Http\Response;
    use Illuminate\Routing\Controller;
    use Laravel\Nova\Contracts\RelatableField;
    use Laravel\Nova\Http\Requests\NovaRequest;

    class FieldController extends Controller
    {
        /**
         * Retrieve the given field for the given resource.
         *
         * @param NovaRequest $request
         * @return Response
         */
        public function show(NovaRequest $request)
        {
            return response()->json(
                $request->newResource()
                    ->availableFields($request)
                    ->when($request->relatable, function ($fields) {
                        return $fields->whereInstanceOf(RelatableField::class);
                    })
                    ->findFieldByAttribute($request->field, function () {
                        abort(404);
                    })
            );
        }
    }
