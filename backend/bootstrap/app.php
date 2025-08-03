<?php

use App\Exceptions\ApiException;
use App\Exceptions\ApiValidationException;
use App\Exceptions\AuthException;
use App\Exceptions\ResourceNotFoundException;
use App\Helpers\ResponseHelper;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'jwt.auth' => \App\Http\Middleware\JwtAuthMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        
        // Handler para AuthenticationException (token inválido/ausente)
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ResponseHelper::unauthorized('Token não fornecido ou inválido');
            }
        });

        // Handler para RouteNotFoundException (quando tenta redirecionar para rota login inexistente)
        $exceptions->render(function (RouteNotFoundException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                // Se a mensagem contém "login" é porque tentou redirecionar para login
                if (str_contains($e->getMessage(), 'login')) {
                    return ResponseHelper::unauthorized('Token não fornecido ou inválido');
                }
                return ResponseHelper::notFound('Rota não encontrada');
            }
        });

        // Handler para ValidationException (dados inválidos)
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ResponseHelper::validationError(
                    $e->errors(),
                    'Dados inválidos fornecidos'
                );
            }
        });

        // Handler para ModelNotFoundException (modelo não encontrado)
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ResponseHelper::notFound('Recurso não encontrado');
            }
        });

        // Handler para NotFoundHttpException (rota não encontrada)
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return ResponseHelper::notFound('Rota não encontrada');
            }
        });

        // Handler para HttpException genérica
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $statusCode = $e->getStatusCode();
                $message = $e->getMessage() ?: 'Erro HTTP';
                return ResponseHelper::error($message, $statusCode, 'HTTP_ERROR');
            }
        });

        // Handler para nossas exceptions customizadas (já têm método render())
        $exceptions->renderable(function (AuthException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $e->render();
            }
        });

        $exceptions->renderable(function (ApiException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $e->render();
            }
        });

        $exceptions->renderable(function (ApiValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $e->render();
            }
        });

        $exceptions->renderable(function (ResourceNotFoundException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return $e->render();
            }
        });

        // Handler genérico para qualquer outra exception
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $message = config('app.debug') 
                    ? $e->getMessage() 
                    : 'Erro interno do servidor';

                return ResponseHelper::error($message, 500, 'INTERNAL_SERVER_ERROR');
            }
        });

    })->create();
