<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
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
                if ($this->isThrottle($e)) {
                    $response = $this->ThrottleResponse($e);
                } elseif ($this->isAuthentication($e)) {
                    $response = $this->AuthResponse($e);
                } elseif ($this->isModel($e)) {
                    $response = $this->ModelResponse($e);
                } elseif ($this->isHttp($e)) {
                    $response = $this->HttpResponse($e);
                } elseif ($e) {
                    $response = $this->IternalResponse($e);
                }
                return $response ? $response : parent::render($request, $e);
            }
        });
    }

    protected function isThrottle($e)
    {
        return $e instanceof ThrottleRequestsException;
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

    protected function IternalResponse($e)
    {
        $message = 'Internal server error';
        $exception = $e->getMessage();
        $line = $e->getLine();
        return response()->error($message, $exception, $line, Response::HTTP_INTERNAL_SERVER_ERROR);
    }


    protected function ThrottleResponse($e)
    {
        return response()->error(
            'Too many attempts, please try after a minute.',
            $e->getMessage(),
            $e->getLine(),
            Response::HTTP_TOO_MANY_REQUESTS
        );
    }

    protected function AuthResponse($e)
    {
        return response()->error(
            'Unauthenticated',
            $e->getMessage(),
            $e->getLine(),
            Response::HTTP_UNAUTHORIZED
        );
    }

    protected function ModelResponse($e)
    {
        return response()->error(
            'Not found',
            $e->getMessage(),
            $e->getLine(),
            Response::HTTP_NOT_FOUND
        );
    }

    protected function HttpResponse($e)
    {
        return response()->error(
            'Incorrect route',
            $e->getMessage(),
            $e->getLine(),
            Response::HTTP_NOT_FOUND
        );
    }
}
