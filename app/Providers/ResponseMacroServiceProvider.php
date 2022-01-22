<?php

namespace App\Providers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class ResponseMacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * Respond with success.
         *
         */
        Response::macro('success', function ($data, $message = '', $status = 200) {
            $response = [
                'success' => true,
                'message' => $message ? $message : 'Success'
            ];
            $response = array_merge($response, [
                'data' => $data
            ]);
            if ($data->resource instanceof AbstractPaginator) {
                $response = array_merge($response, [
                    'meta' => [
                        'current_page'  => $data->currentPage(),
                        'last_page'     => $data->lastPage(),
                        'per_page'      => $data->perPage(),
                        'total'         => $data->total(),
                    ],
                    'links' => [
                        'first_page'    => (string) $data->getOptions()['path']
                            . '?'
                            . $data->getOptions()['pageName']
                            . "=1",
                        'prev_page'     => (string) $data->previousPageUrl(),
                        'next_page'     => (string) $data->nextPageUrl(),
                        'last_page'     => (string) $data->getOptions()['path']
                            . '?'
                            . $data->getOptions()['pageName']
                            . "={$data->lastPage()}",
                    ]
                ]);
            }
            return Response::json($response, $status);
        });

        /**
         * Respond with error.
         *
         */
        Response::macro('error', function ($message = '', $exception = '', $line = '', $status = 400) {
            $response = [
                'success' => false,
                'message' => $message ? $message : 'Failed'
            ];
            if (config('app.env') != 'production') {
                if ($exception) {
                    $response = array_merge($response, [
                        'exception' => $exception
                    ]);
                }
                if ($line) {
                    $response = array_merge($response, [
                        'line' => $line
                    ]);
                }
            }
            return Response::json($response, $status);
        });

        /**
         * Respond with validation.
         *
         */
        Response::macro('validation', function ($errors) {
            $response = [
                'success' => false,
                'message' => 'Validation error',
                'errors' => $errors
            ];
            throw new HttpResponseException(Response::json($response, HttpResponse::HTTP_UNPROCESSABLE_ENTITY));
        });
    }
}
