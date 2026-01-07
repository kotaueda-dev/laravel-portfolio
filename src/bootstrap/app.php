<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(append: [
            \App\Http\Middleware\LogContextMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 全ての例外をJSONで返したい場合（API専用サーバーなら）
        $exceptions->shouldRenderJsonWhen(fn ($request, $e) => $request->is('api/*'));

        // 401: AuthenticationException (認証エラー)
        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        });

        // 403: AccessDeniedHttpException (権限エラー)
        $exceptions->render(function (AccessDeniedHttpException $e) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        });
        $exceptions->render(function (AuthorizationException $e) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 403);
        });

        // 404: NotFoundHttpException (リソースなし)
        $exceptions->render(function (NotFoundHttpException $e) {
            return response()->json([
                'message' => 'Not found.',
            ], 404);
        });

        // 422: ValidationException (バリデーションエラー)
        $exceptions->render(function (ValidationException $e) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        });

        // 400: その他 BadRequest
        $exceptions->render(function (BadRequestHttpException $e) {
            return response()->json([
                'message' => 'Invalid parameter.',
            ], 400);
        });
    })->create();
