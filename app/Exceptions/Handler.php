<?php

namespace App\Exceptions;

use App\Dictionary\Exceptions\EAppDictionary;
use App\Http\Common\Response\SimpleAppException;
use App\Mapping\Exception\ApiExceptionDTO;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->renderable(function (Throwable $e) { // NOSONAR
            if ($e instanceof ValidationException) {
                return $this->validationJsonResponse($e);
            }

            return $this->defaultJsonResponse($e);
        });
    }

    protected function validationJsonResponse(ValidationException $e): JsonResponse // NOSONAR
    {
        return response()->json([
            'error_code' => 'validation-error',
            'message' => $e->validator->errors()->toArray()
        ],
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    protected function defaultJsonResponse(Throwable $e): JsonResponse // NOSONAR
    {
        $code = Response::HTTP_BAD_REQUEST;
        if ($e instanceof UnauthorizedException) {
            $code = 401;
        }

        return response()->json(
            [
                'error_code' => $e->getCode(),
                'message' => $e->getMessage()
            ],
            $code
        );
    }
}
