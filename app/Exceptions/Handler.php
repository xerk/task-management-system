<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
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
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e): JsonResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
    {
        // Handle API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions with consistent JSON responses
     */
    private function handleApiException(Request $request, Throwable $e): JsonResponse
    {
        // Validation exceptions
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }

        // Not found exceptions
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found.',
                'error' => 'The requested resource was not found.'
            ], 404);
        }

        // Method not allowed exceptions
        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Method not allowed.',
                'error' => 'The HTTP method is not allowed for this route.'
            ], 405);
        }

        // Unauthorized exceptions
        if ($e instanceof UnauthorizedHttpException) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
                'error' => 'Authentication required.'
            ], 401);
        }

        // Default server error
        $statusCode = 500;
        if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
            $statusCode = $e->getStatusCode();
        }

        return response()->json([
            'success' => false,
            'message' => 'An error occurred.',
            'error' => config('app.debug') ? $e->getMessage() : 'Internal server error.'
        ], $statusCode);
    }
}
