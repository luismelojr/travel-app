<x-mail::message>
# {{ $travelRequest->status === App\Enums\TravelRequestStatusEnum::APPROVED ? '✅ Pedido de Viagem Aprovado' : '❌ Pedido de Viagem Cancelado' }}

Olá **{{ $travelRequest->user->name }}**,

@if ($travelRequest->status === App\Enums\TravelRequestStatusEnum::APPROVED)
Temos uma boa notícia! Seu pedido de viagem foi **aprovado**.
@else
Informamos que seu pedido de viagem foi **cancelado**.
@endif

## 📋 Detalhes do Pedido

**Solicitante:** {{ $travelRequest->requester_name }}  
**Destino:** {{ $travelRequest->destination }}  
**Data de Partida:** {{ $travelRequest->departure_date->format('d/m/Y') }}  
**Data de Retorno:** {{ $travelRequest->return_date->format('d/m/Y') }}  
**Duração:** {{ $travelRequest->getDurationInDays() }} {{ $travelRequest->getDurationInDays() > 1 ? 'dias' : 'dia' }}

**Status Anterior:** {{ $previousStatus->label() }}  
**Status Atual:** {{ $travelRequest->status->label() }}

@if ($travelRequest->notes)
**Observações:** {{ $travelRequest->notes }}
@endif

---

@if ($travelRequest->status === App\Enums\TravelRequestStatusEnum::APPROVED)
🎉 **Próximos passos:**
- Aguarde instruções adicionais sobre reservas e documentação necessária
- Entre em contato com o RH caso tenha dúvidas
@else
ℹ️ **Em caso de dúvidas:**
- Entre em contato com seu gestor ou com o departamento de RH
- Você pode criar um novo pedido se necessário
@endif

Atenciosamente,<br>
**{{ config('app.name') }}**
</x-mail::message>
