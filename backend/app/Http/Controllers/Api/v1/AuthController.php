<?php

namespace App\Http\Controllers\Api\v1;

use App\Contracts\AuthServiceInterface;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Travel API",
    description: "API para gerenciamento de solicitações de viagem"
)]
#[OA\Server(
    url: "/api/v1",
    description: "API v1"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    name: "Authorization",
    in: "header",
    scheme: "bearer",
    bearerFormat: "JWT"
)]
class AuthController extends Controller
{
    private AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    #[OA\Post(
        path: "/auth/register",
        summary: "Registrar novo usuário",
        description: "Cria uma nova conta de usuário e retorna o token de acesso",
        tags: ["Autenticação"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "email", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Luis Henrique", description: "Nome completo do usuário"),
                    new OA\Property(property: "email", type: "string", format: "email", example: "luis@example.com", description: "Email do usuário"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "MinhaSenh@123", description: "Senha do usuário (mínimo 8 caracteres, deve conter maiúscula, minúscula, número e caractere especial)"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "MinhaSenh@123", description: "Confirmação da senha")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Usuário registrado com sucesso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Usuário registrado e logado com sucesso"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(
                                    property: "user",
                                    properties: [
                                        new OA\Property(property: "id", type: "integer", example: 1),
                                        new OA\Property(property: "name", type: "string", example: "Luis Henrique"),
                                        new OA\Property(property: "email", type: "string", example: "luis@example.com"),
                                        new OA\Property(property: "role", type: "string", example: "user"),
                                        new OA\Property(property: "role_label", type: "string", example: "Usuário"),
                                        new OA\Property(property: "email_verified_at", type: "string", nullable: true, example: null),
                                        new OA\Property(property: "created_at", type: "string", example: "2024-01-01 10:00:00"),
                                        new OA\Property(property: "updated_at", type: "string", example: "2024-01-01 10:00:00")
                                    ],
                                    type: "object"
                                ),
                                new OA\Property(property: "token", type: "string", example: "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
                                new OA\Property(property: "token_type", type: "string", example: "bearer"),
                                new OA\Property(property: "expires_in", type: "integer", example: 3600)
                            ],
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Dados de entrada inválidos",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Dados de validação inválidos"),
                        new OA\Property(property: "errors", type: "object", example: ["email" => ["O email já está sendo usado por outro usuário."]])
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: "Erro interno do servidor",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Erro interno do servidor"),
                        new OA\Property(property: "error_code", type: "string", example: "REGISTRATION_ERROR")
                    ]
                )
            )
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());

            return ResponseHelper::created($result, 'Usuário registrado e logado com sucesso');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500, 'REGISTRATION_ERROR');
        }
    }

    #[OA\Post(
        path: "/auth/login",
        summary: "Fazer login",
        description: "Autentica um usuário e retorna o token de acesso",
        tags: ["Autenticação"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "password"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "luis@example.com", description: "Email do usuário"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "MinhaSenh@123", description: "Senha do usuário")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Login realizado com sucesso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Login realizado com sucesso"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(
                                    property: "user",
                                    properties: [
                                        new OA\Property(property: "id", type: "integer", example: 1),
                                        new OA\Property(property: "name", type: "string", example: "Luis Henrique"),
                                        new OA\Property(property: "email", type: "string", example: "luis@example.com"),
                                        new OA\Property(property: "role", type: "string", example: "user"),
                                        new OA\Property(property: "role_label", type: "string", example: "Usuário"),
                                        new OA\Property(property: "email_verified_at", type: "string", nullable: true, example: null),
                                        new OA\Property(property: "created_at", type: "string", example: "2024-01-01 10:00:00"),
                                        new OA\Property(property: "updated_at", type: "string", example: "2024-01-01 10:00:00")
                                    ],
                                    type: "object"
                                ),
                                new OA\Property(property: "token", type: "string", example: "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
                                new OA\Property(property: "token_type", type: "string", example: "bearer"),
                                new OA\Property(property: "expires_in", type: "integer", example: 3600)
                            ],
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Credenciais inválidas",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Credenciais inválidas")
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: "Dados de entrada inválidos",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Dados de validação inválidos"),
                        new OA\Property(property: "errors", type: "object", example: ["email" => ["O email é obrigatório."]])
                    ]
                )
            )
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());

            return ResponseHelper::success($result, 'Login realizado com sucesso');
        } catch (\Exception $e) {
            return ResponseHelper::unauthorized($e->getMessage());
        }
    }

    #[OA\Post(
        path: "/auth/refresh",
        summary: "Renovar token",
        description: "Renova o token de acesso JWT do usuário autenticado",
        tags: ["Autenticação"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Token renovado com sucesso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Token renovado com sucesso"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(
                                    property: "user",
                                    properties: [
                                        new OA\Property(property: "id", type: "integer", example: 1),
                                        new OA\Property(property: "name", type: "string", example: "Luis Henrique"),
                                        new OA\Property(property: "email", type: "string", example: "luis@example.com"),
                                        new OA\Property(property: "role", type: "string", example: "user"),
                                        new OA\Property(property: "role_label", type: "string", example: "Usuário"),
                                        new OA\Property(property: "email_verified_at", type: "string", nullable: true, example: null),
                                        new OA\Property(property: "created_at", type: "string", example: "2024-01-01 10:00:00"),
                                        new OA\Property(property: "updated_at", type: "string", example: "2024-01-01 10:00:00")
                                    ],
                                    type: "object"
                                ),
                                new OA\Property(property: "token", type: "string", example: "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
                                new OA\Property(property: "token_type", type: "string", example: "bearer"),
                                new OA\Property(property: "expires_in", type: "integer", example: 3600)
                            ],
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Token inválido ou expirado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Token inválido ou expirado")
                    ]
                )
            )
        ]
    )]
    public function refresh(): JsonResponse
    {
        try {
            $result = $this->authService->refresh();

            return ResponseHelper::success($result, 'Token renovado com sucesso');
        } catch (\Exception $e) {
            return ResponseHelper::unauthorized($e->getMessage());
        }
    }

    #[OA\Post(
        path: "/auth/logout",
        summary: "Fazer logout",
        description: "Invalida o token JWT do usuário autenticado",
        tags: ["Autenticação"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Logout realizado com sucesso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Logout realizado com sucesso"),
                        new OA\Property(property: "data", type: "null", example: null)
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Token inválido ou expirado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Token inválido ou expirado")
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Erro no logout",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Erro no logout"),
                        new OA\Property(property: "error_code", type: "string", example: "LOGOUT_ERROR")
                    ]
                )
            )
        ]
    )]
    public function logout(): JsonResponse
    {
        try {
            $this->authService->logout();

            return ResponseHelper::success(null, 'Logout realizado com sucesso');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 400, 'LOGOUT_ERROR');
        }
    }

    #[OA\Get(
        path: "/auth/me",
        summary: "Obter dados do usuário logado",
        description: "Retorna os dados do usuário atualmente autenticado",
        tags: ["Autenticação"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Dados do usuário obtidos com sucesso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Dados do usuário obtidos com sucesso"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "name", type: "string", example: "Luis Henrique"),
                                new OA\Property(property: "email", type: "string", example: "luis@example.com"),
                                new OA\Property(property: "role", type: "string", example: "user"),
                                new OA\Property(property: "role_label", type: "string", example: "Usuário"),
                                new OA\Property(property: "email_verified_at", type: "string", nullable: true, example: null),
                                new OA\Property(property: "created_at", type: "string", example: "2024-01-01 10:00:00"),
                                new OA\Property(property: "updated_at", type: "string", example: "2024-01-01 10:00:00")
                            ],
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Token inválido ou expirado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Token inválido ou expirado")
                    ]
                )
            )
        ]
    )]
    public function me(): JsonResponse
    {
        try {
            $result = $this->authService->me();

            return ResponseHelper::success($result, 'Dados do usuário obtidos com sucesso');
        } catch (\Exception $e) {
            return ResponseHelper::unauthorized($e->getMessage());
        }
    }
}
