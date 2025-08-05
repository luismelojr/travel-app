<?php

namespace App\Http\Controllers\Api\v1;

use App\Contracts\TravelRequestServiceInterface;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\TravelRequest\CreateTravelRequestRequest;
use App\Http\Requests\TravelRequest\UpdateStatusRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use OpenApi\Attributes as OA;

class TravelRequestController extends Controller
{
    private TravelRequestServiceInterface $travelRequestService;

    public function __construct(TravelRequestServiceInterface $travelRequestService)
    {
        $this->travelRequestService = $travelRequestService;
    }

    #[OA\Post(
        path: "/travel-requests",
        summary: "Criar novo pedido de viagem",
        description: "Cria um novo pedido de viagem para o usuário autenticado",
        tags: ["Pedidos de Viagem"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["requester_name", "destination", "departure_date", "return_date"],
                properties: [
                    new OA\Property(property: "requester_name", type: "string", example: "Luis Henrique", description: "Nome do solicitante"),
                    new OA\Property(property: "destination", type: "string", example: "São Paulo, SP", description: "Destino da viagem"),
                    new OA\Property(property: "departure_date", type: "string", format: "date", example: "2024-12-25", description: "Data de partida (formato: YYYY-MM-DD)"),
                    new OA\Property(property: "return_date", type: "string", format: "date", example: "2024-12-30", description: "Data de retorno (formato: YYYY-MM-DD)"),
                    new OA\Property(property: "notes", type: "string", example: "Viagem para reunião de negócios", description: "Observações adicionais (opcional)", nullable: true)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Pedido de viagem criado com sucesso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Pedido de viagem criado com sucesso"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "requester_name", type: "string", example: "Luis Henrique"),
                                new OA\Property(property: "destination", type: "string", example: "São Paulo, SP"),
                                new OA\Property(property: "departure_date", type: "string", example: "2024-12-25"),
                                new OA\Property(property: "return_date", type: "string", example: "2024-12-30"),
                                new OA\Property(
                                    property: "status",
                                    properties: [
                                        new OA\Property(property: "value", type: "string", example: "requested"),
                                        new OA\Property(property: "label", type: "string", example: "Solicitado")
                                    ],
                                    type: "object"
                                ),
                                new OA\Property(property: "notes", type: "string", example: "Viagem para reunião de negócios", nullable: true),
                                new OA\Property(property: "duration_days", type: "integer", example: 5),
                                new OA\Property(property: "created_at", type: "string", example: "2024-01-01T10:00:00.000000Z"),
                                new OA\Property(property: "updated_at", type: "string", example: "2024-01-01T10:00:00.000000Z")
                            ],
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Não autenticado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Token inválido ou expirado")
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
                        new OA\Property(property: "errors", type: "object", example: ["departure_date" => ["A data de partida não pode ser anterior a hoje."]])
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
                        new OA\Property(property: "error_code", type: "string", example: "TRAVEL_REQUEST_CREATION_ERROR")
                    ]
                )
            )
        ]
    )]
    public function store(CreateTravelRequestRequest $request): JsonResponse
    {
        try {
            $travelRequest = $this->travelRequestService->create($request->validated());

            return ResponseHelper::created($travelRequest, 'Pedido de viagem criado com sucesso');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500, 'TRAVEL_REQUEST_CREATION_ERROR');
        }
    }

    #[OA\Get(
        path: "/travel-requests/{id}",
        summary: "Buscar pedido de viagem por ID",
        description: "Retorna os detalhes de um pedido de viagem específico",
        tags: ["Pedidos de Viagem"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID do pedido de viagem",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Pedido de viagem encontrado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Pedido de viagem encontrado"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "requester_name", type: "string", example: "Luis Henrique"),
                                new OA\Property(property: "destination", type: "string", example: "São Paulo, SP"),
                                new OA\Property(property: "departure_date", type: "string", example: "2024-12-25"),
                                new OA\Property(property: "return_date", type: "string", example: "2024-12-30"),
                                new OA\Property(
                                    property: "status",
                                    properties: [
                                        new OA\Property(property: "value", type: "string", example: "requested"),
                                        new OA\Property(property: "label", type: "string", example: "Solicitado")
                                    ],
                                    type: "object"
                                ),
                                new OA\Property(property: "notes", type: "string", example: "Viagem para reunião de negócios", nullable: true),
                                new OA\Property(property: "duration_days", type: "integer", example: 5),
                                new OA\Property(
                                    property: "user",
                                    properties: [
                                        new OA\Property(property: "id", type: "integer", example: 1),
                                        new OA\Property(property: "name", type: "string", example: "Luis Henrique"),
                                        new OA\Property(property: "email", type: "string", example: "luis@example.com"),
                                        new OA\Property(property: "role", type: "string", example: "user"),
                                        new OA\Property(property: "role_label", type: "string", example: "Usuário")
                                    ],
                                    type: "object"
                                ),
                                new OA\Property(property: "created_at", type: "string", example: "2024-01-01T10:00:00.000000Z"),
                                new OA\Property(property: "updated_at", type: "string", example: "2024-01-01T10:00:00.000000Z")
                            ],
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Não autenticado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Token inválido ou expirado")
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Sem permissão para visualizar",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Você não tem permissão para visualizar este pedido")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Pedido não encontrado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Pedido de viagem não encontrado")
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
                        new OA\Property(property: "error_code", type: "string", example: "TRAVEL_REQUEST_SHOW_ERROR")
                    ]
                )
            )
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->travelRequestService->findById($id);
            return ResponseHelper::success($result, 'Pedido de viagem encontrado');

        } catch (\App\Exceptions\ResourceNotFoundException $e) {
            return ResponseHelper::notFound('Pedido de viagem não encontrado');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return ResponseHelper::forbidden('Você não tem permissão para visualizar este pedido');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500, 'TRAVEL_REQUEST_SHOW_ERROR');
        }
    }

    #[OA\Get(
        path: "/travel-requests",
        summary: "Listar pedidos de viagem",
        description: "Lista todos os pedidos de viagem com filtros opcionais. Usuários comuns veem apenas seus próprios pedidos, administradores veem todos.",
        tags: ["Pedidos de Viagem"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "status",
                in: "query",
                required: false,
                description: "Filtrar por status",
                schema: new OA\Schema(type: "string", enum: ["requested", "approved", "cancelled"], example: "requested")
            ),
            new OA\Parameter(
                name: "destination",
                in: "query",
                required: false,
                description: "Filtrar por destino",
                schema: new OA\Schema(type: "string", example: "São Paulo")
            ),
            new OA\Parameter(
                name: "date_from",
                in: "query",
                required: false,
                description: "Filtrar pedidos com data de partida a partir desta data",
                schema: new OA\Schema(type: "string", format: "date", example: "2024-01-01")
            ),
            new OA\Parameter(
                name: "date_to",
                in: "query",
                required: false,
                description: "Filtrar pedidos com data de partida até esta data",
                schema: new OA\Schema(type: "string", format: "date", example: "2024-12-31")
            ),
            new OA\Parameter(
                name: "request_date_from",
                in: "query",
                required: false,
                description: "Filtrar pedidos criados a partir desta data",
                schema: new OA\Schema(type: "string", format: "date", example: "2024-01-01")
            ),
            new OA\Parameter(
                name: "request_date_to",
                in: "query",
                required: false,
                description: "Filtrar pedidos criados até esta data",
                schema: new OA\Schema(type: "string", format: "date", example: "2024-12-31")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de pedidos de viagem obtida com sucesso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Lista de pedidos de viagem obtida com sucesso"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 1),
                                    new OA\Property(property: "requester_name", type: "string", example: "Luis Henrique"),
                                    new OA\Property(property: "destination", type: "string", example: "São Paulo, SP"),
                                    new OA\Property(property: "departure_date", type: "string", example: "2024-12-25"),
                                    new OA\Property(property: "return_date", type: "string", example: "2024-12-30"),
                                    new OA\Property(
                                        property: "status",
                                        properties: [
                                            new OA\Property(property: "value", type: "string", example: "requested"),
                                            new OA\Property(property: "label", type: "string", example: "Solicitado")
                                        ],
                                        type: "object"
                                    ),
                                    new OA\Property(property: "notes", type: "string", example: "Viagem para reunião de negócios", nullable: true),
                                    new OA\Property(property: "duration_days", type: "integer", example: 5),
                                    new OA\Property(property: "created_at", type: "string", example: "2024-01-01T10:00:00.000000Z"),
                                    new OA\Property(property: "updated_at", type: "string", example: "2024-01-01T10:00:00.000000Z")
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Não autenticado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Token inválido ou expirado")
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
                        new OA\Property(property: "error_code", type: "string", example: "TRAVEL_REQUEST_LIST_ERROR")
                    ]
                )
            )
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'status',
                'destination',
                'date_from',
                'date_to',
                'request_date_from',
                'request_date_to'
            ]);

            $travelRequests = $this->travelRequestService->list($filters);

            return ResponseHelper::success($travelRequests, 'Lista de pedidos de viagem obtida com sucesso');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500, 'TRAVEL_REQUEST_LIST_ERROR');
        }
    }

    #[OA\Patch(
        path: "/travel-requests/{id}/status",
        summary: "Atualizar status do pedido (Admin)",
        description: "Atualiza o status de um pedido de viagem. Apenas administradores podem usar este endpoint.",
        tags: ["Pedidos de Viagem"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID do pedido de viagem",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["status"],
                properties: [
                    new OA\Property(
                        property: "status",
                        type: "string",
                        enum: ["requested", "approved", "cancelled"],
                        example: "approved",
                        description: "Novo status do pedido"
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Status atualizado com sucesso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Status do pedido de viagem atualizado com sucesso"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "requester_name", type: "string", example: "Luis Henrique"),
                                new OA\Property(property: "destination", type: "string", example: "São Paulo, SP"),
                                new OA\Property(property: "departure_date", type: "string", example: "2024-12-25"),
                                new OA\Property(property: "return_date", type: "string", example: "2024-12-30"),
                                new OA\Property(
                                    property: "status",
                                    properties: [
                                        new OA\Property(property: "value", type: "string", example: "approved"),
                                        new OA\Property(property: "label", type: "string", example: "Aprovado")
                                    ],
                                    type: "object"
                                ),
                                new OA\Property(property: "notes", type: "string", example: "Viagem para reunião de negócios", nullable: true),
                                new OA\Property(property: "duration_days", type: "integer", example: 5),
                                new OA\Property(property: "created_at", type: "string", example: "2024-01-01T10:00:00.000000Z"),
                                new OA\Property(property: "updated_at", type: "string", example: "2024-01-01T10:30:00.000000Z")
                            ],
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Não autenticado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Token inválido ou expirado")
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Sem permissão (apenas administradores)",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Apenas administradores podem alterar o status dos pedidos")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Pedido não encontrado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Pedido de viagem não encontrado")
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
                        new OA\Property(property: "errors", type: "object", example: ["status" => ["O status deve ser: solicitado, aprovado ou cancelado."]])
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Erro na atualização do status",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Erro na atualização do status"),
                        new OA\Property(property: "error_code", type: "string", example: "TRAVEL_REQUEST_STATUS_UPDATE_ERROR")
                    ]
                )
            )
        ]
    )]
    public function updateStatus(UpdateStatusRequest $request, int $id): JsonResponse
    {
        try {
            $result = $this->travelRequestService->updateStatus($id, $request->validated()['status']);
            return ResponseHelper::success($result, 'Status do pedido de viagem atualizado com sucesso');

        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'não encontrado')) {
                return ResponseHelper::notFound($e->getMessage());
            }

            if (str_contains($e->getMessage(), 'Apenas administradores')) {
                return ResponseHelper::forbidden($e->getMessage());
            }

            return ResponseHelper::error($e->getMessage(), 400, 'TRAVEL_REQUEST_STATUS_UPDATE_ERROR');
        }
    }

    #[OA\Patch(
        path: "/travel-requests/{id}/cancel",
        summary: "Cancelar pedido de viagem",
        description: "Cancela um pedido de viagem. Usuários podem cancelar seus próprios pedidos não aprovados, administradores podem cancelar qualquer pedido não aprovado.",
        tags: ["Pedidos de Viagem"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "ID do pedido de viagem",
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Pedido cancelado com sucesso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Pedido de viagem cancelado com sucesso"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "requester_name", type: "string", example: "Luis Henrique"),
                                new OA\Property(property: "destination", type: "string", example: "São Paulo, SP"),
                                new OA\Property(property: "departure_date", type: "string", example: "2024-12-25"),
                                new OA\Property(property: "return_date", type: "string", example: "2024-12-30"),
                                new OA\Property(
                                    property: "status",
                                    properties: [
                                        new OA\Property(property: "value", type: "string", example: "cancelled"),
                                        new OA\Property(property: "label", type: "string", example: "Cancelado")
                                    ],
                                    type: "object"
                                ),
                                new OA\Property(property: "notes", type: "string", example: "Viagem para reunião de negócios", nullable: true),
                                new OA\Property(property: "duration_days", type: "integer", example: 5),
                                new OA\Property(property: "created_at", type: "string", example: "2024-01-01T10:00:00.000000Z"),
                                new OA\Property(property: "updated_at", type: "string", example: "2024-01-01T10:45:00.000000Z")
                            ],
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Não autenticado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Token inválido ou expirado")
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Sem permissão para cancelar",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Você não tem permissão para cancelar este pedido")
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: "Pedido não encontrado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Pedido de viagem não encontrado")
                    ]
                )
            ),
            new OA\Response(
                response: 400,
                description: "Erro no cancelamento",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Não é possível cancelar este pedido"),
                        new OA\Property(property: "error_code", type: "string", example: "TRAVEL_REQUEST_CANCEL_ERROR")
                    ]
                )
            )
        ]
    )]
    public function cancel(int $id): JsonResponse
    {
        try {
            $result = $this->travelRequestService->cancel($id);
            return ResponseHelper::success($result, 'Pedido de viagem cancelado com sucesso');

        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'não encontrado')) {
                return ResponseHelper::notFound($e->getMessage());
            }

            if (str_contains($e->getMessage(), 'não tem permissão')) {
                return ResponseHelper::forbidden($e->getMessage());
            }

            return ResponseHelper::error($e->getMessage(), 400, 'TRAVEL_REQUEST_CANCEL_ERROR');
        }
    }

    #[OA\Get(
        path: "/travel-requests/stats",
        summary: "Buscar estatísticas dos pedidos de viagem",
        description: "Retorna as estatísticas dos pedidos de viagem do usuário autenticado (total, pendentes, aprovados e cancelados)",
        tags: ["Pedidos de Viagem"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Estatísticas obtidas com sucesso",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Estatísticas obtidas com sucesso"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "total", type: "integer", example: 25),
                                new OA\Property(property: "pending", type: "integer", example: 5),
                                new OA\Property(property: "approved", type: "integer", example: 15),
                                new OA\Property(property: "cancelled", type: "integer", example: 5)
                            ],
                            type: "object"
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: "Não autenticado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "success", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Token inválido ou expirado")
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
                        new OA\Property(property: "error_code", type: "string", example: "TRAVEL_REQUEST_STATS_ERROR")
                    ]
                )
            )
        ]
    )]
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->travelRequestService->getStats();
            return ResponseHelper::success($stats, 'Estatísticas obtidas com sucesso');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500, 'TRAVEL_REQUEST_STATS_ERROR');
        }
    }
}
