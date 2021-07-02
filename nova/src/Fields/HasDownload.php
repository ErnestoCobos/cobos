<?php

    namespace Laravel\Nova\Fields;

    use Illuminate\Http\Response;
    use Laravel\Nova\Http\Requests\NovaRequest;
    use Laravel\Nova\Resource;

    trait HasDownload
    {
        /**
         * The callback used to generate the download HTTP response.
         *
         * @var callable
         */
        public $downloadResponseCallback;

        /**
         * Determin if the file is able to be downloaded.
         *
         * @var bool
         */
        public $downloadsAreEnabled = true;

        /**
         * Disable downloading the file.
         *
         * @return $this
         */
        public function disableDownload()
        {
            $this->downloadsAreEnabled = false;

            return $this;
        }

        /**
         * Specify the callback that should be used to create a download HTTP response.
         *
         * @param callable $downloadResponseCallback
         * @return $this
         */
        public function download(callable $downloadResponseCallback)
        {
            $this->downloadResponseCallback = $downloadResponseCallback;

            return $this;
        }

        /**
         * Create an HTTP response to download the underlying field.
         *
         * @param NovaRequest $request
         * @param Resource $resource
         * @return Response
         */
        public function toDownloadResponse(NovaRequest $request, $resource)
        {
            return call_user_func(
                $this->downloadResponseCallback,
                $request,
                $resource->resource,
                $this->getStorageDisk(),
                $this->getStoragePath()
            );
        }
    }
