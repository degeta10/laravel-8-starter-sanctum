<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $env;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->env = config('app.env');

        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($request->wantsJson()) {
                $response = "";
                if ($this->isAuthentication($e)) {
                    $response = $this->AuthResponse($e);
                } else if ($this->isModel($e)) {
                    $response = $this->ModelResponse($e);
                } else if ($this->isHttp($e)) {
                    $response = $this->HttpResponse($e);
                }
                return $response ? $response : parent::render($request, $e);
            }
        });
    }

    protected function isAuthentication($e)
    {
        return $e instanceof AuthenticationException;
    }

    protected function isModel($e)
    {
        return $e instanceof ModelNotFoundException;
    }

    protected function isHttp($e)
    {
        return $e instanceof NotFoundHttpException;
    }

    protected function AuthResponse($e)
    {
        $response = ['message' => 'Unauthenticated'];
        if ($this->env != 'production') {
            $response = array_merge($response, [
                'exception' => $e->getMessage()
            ]);
        }
        return new JsonResponse(
            $response,
            Response::HTTP_UNAUTHORIZED
        );
    }

    protected function ModelResponse($e)
    {
        $response = ['message' => 'Not found'];
        if ($this->env != 'production') {
            $response = array_merge($response, [
                'exception' => $e->getMessage()
            ]);
        }
        return new JsonResponse(
            $response,
            Response::HTTP_NOT_FOUND
        );
    }

    protected function HttpResponse($e)
    {
        $response = ['message' => 'Incorrect route'];
        if ($this->env != 'production') {
            $response = array_merge($response, [
                'exception' => $e->getMessage()
            ]);
        }
        return new JsonResponse(
            $response,
            Response::HTTP_NOT_FOUND
        );
    }

    protected function GeneralResponse($e)
    {
        $response = ['message' => 'Something went wrong'];
        $response = array_merge($response, [
            'exception' => $e->getMessage()
        ]);
        return new JsonResponse(
            $response,
            Response::HTTP_BAD_REQUEST
        );
    }
}
