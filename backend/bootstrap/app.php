<?php

use App\Http\Middleware\LogContextMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(append: [
            LogContextMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 全ての例外をJSONで返したい場合（API専用サーバーなら）
        $exceptions->shouldRenderJsonWhen(fn ($request, $e) => $request->is('api/*'));

        // 401: AuthenticationException (認証エラー)
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/login')) {
                return response()->json([
                    'message' => __('errors.invalid_credentials'),
                ], 401);
            }

            return response()->json([
                'message' => __('errors.unauthenticated'),
            ], 401);
        });

        // 403: AccessDeniedHttpException (権限エラー)
        $exceptions->render(function (AccessDeniedHttpException $e) {
            return response()->json([
                'message' => __('errors.unauthorized'),
            ], 403);
        });
        $exceptions->render(function (AuthorizationException $e) {
            return response()->json([
                'message' => __('errors.unauthorized'),
            ], 403);
        });

        // 404: NotFoundHttpException (リソースなし)
        $exceptions->render(function (NotFoundHttpException $e) {
            return response()->json([
                'message' => __('errors.not_found'),
            ], 404);
        });

        // 422: ValidationException (バリデーションエラー)
        $exceptions->render(function (ValidationException $e) {
            return response()->json([
                'message' => __('errors.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        });

        // 400: Bad Request
        $exceptions->render(function (BadRequestHttpException $e) {
            return response()->json([
                'message' => __('errors.invalid_parameter'),
            ], 400);
        });

        // 405: Method Not Allowed
        $exceptions->render(function (MethodNotAllowedHttpException $e) {
            return response()->json([
                'message' => __('errors.method_not_allowed'),
            ], 405);
        });

        // 409: Conflict
        $exceptions->render(function (ConflictHttpException $e) {
            return response()->json([
                'message' => __('errors.conflict'),
            ], 409);
        });

        // 410: Gone
        $exceptions->render(function (GoneHttpException $e) {
            return response()->json([
                'message' => __('errors.gone'),
            ], 410);
        });

        // 415: Unsupported Media Type
        $exceptions->render(function (UnsupportedMediaTypeHttpException $e) {
            return response()->json([
                'message' => __('errors.unsupported_media_type'),
            ], 415);
        });

        // 429: Too Many Requests
        $exceptions->render(function (TooManyRequestsHttpException $e) {
            return response()->json([
                'message' => __('errors.too_many_requests'),
            ], 429);
        });

        // その他の汎用HTTP例外
        $exceptions->render(function (HttpException $e) {
            $statusCode = $e->getStatusCode();
            $messageKey = match ($statusCode) {
                401 => 'errors.unauthenticated',
                403 => 'errors.unauthorized',
                404 => 'errors.not_found',
                405 => 'errors.method_not_allowed',
                408 => 'errors.request_timeout',
                409 => 'errors.conflict',
                410 => 'errors.gone',
                413 => 'errors.payload_too_large',
                415 => 'errors.unsupported_media_type',
                429 => 'errors.too_many_requests',
                503 => 'errors.service_unavailable',
                default => 'errors.internal_error',
            };

            return response()->json([
                'message' => __($messageKey),
            ], $statusCode);
        });

        $exceptions->render(function (Throwable $e) {

            Log::error('予期しない例外が発生しました。', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'type' => get_class($e),
            ]);

            return response()->json([
                'message' => __('errors.internal_error'),
            ], 500);
        });
    })->create();
