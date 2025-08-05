<?php

namespace Database\Seeders;

use App\Enums\TravelRequestStatusEnum;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoTravelRequestsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', 'user')->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('Nenhum usuário comum encontrado. Execute o DemoUsersSeeder primeiro.');
            return;
        }

        $destinations = [
            'São Paulo, SP',
            'Rio de Janeiro, RJ',
            'Brasília, DF',
            'Salvador, BA',
            'Fortaleza, CE',
            'Belo Horizonte, MG',
            'Manaus, AM',
            'Curitiba, PR',
            'Recife, PE',
            'Goiânia, GO'
        ];

        $notes = [
            'Reunião de negócios com cliente importante',
            'Treinamento técnico na matriz da empresa',
            'Participação em feira comercial do setor',
            'Visita técnica a fornecedor estratégico',
            'Apresentação de projeto para investidores',
            'Conferência anual da área',
            'Auditoria em filial regional',
            'Abertura de nova unidade comercial',
            null, // Algumas solicitações sem observações
            'Curso de capacitação profissional'
        ];

        // Criar algumas solicitações de exemplo
        foreach ($users as $user) {
            // 2-4 solicitações por usuário
            $requestCount = rand(2, 4);
            
            for ($i = 0; $i < $requestCount; $i++) {
                $departureDate = now()->addDays(rand(7, 90));
                $returnDate = $departureDate->copy()->addDays(rand(1, 14));
                
                // Distribuir status de forma realista
                $rand = rand(1, 100);
                if ($rand <= 50) {
                    $status = TravelRequestStatusEnum::REQUESTED;
                } elseif ($rand <= 85) {
                    $status = TravelRequestStatusEnum::APPROVED;
                } else {
                    $status = TravelRequestStatusEnum::CANCELLED;
                }
                
                TravelRequest::create([
                    'user_id' => $user->id,
                    'requester_name' => $user->name,
                    'destination' => $destinations[array_rand($destinations)],
                    'departure_date' => $departureDate->format('Y-m-d'),
                    'return_date' => $returnDate->format('Y-m-d'),
                    'notes' => $notes[array_rand($notes)],
                    'status' => $status,
                    'created_at' => now()->subDays(rand(0, 30)), // Criadas nos últimos 30 dias
                    'updated_at' => now()->subDays(rand(0, 15)),
                ]);
            }
        }

        $totalRequests = TravelRequest::count();
        $this->command->info("$totalRequests solicitações de viagem de demonstração criadas com sucesso!");
    }
}