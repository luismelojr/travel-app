<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar usuário administrador para demonstração
        User::firstOrCreate(
            ['email' => 'admin@travel.com'],
            [
                'name' => 'Administrador do Sistema',
                'email' => 'admin@travel.com',
                'password' => Hash::make('admin123'),
                'role' => UserRoleEnum::ADMIN,
                'email_verified_at' => now(),
            ]
        );

        // Criar usuário comum para demonstração
        User::firstOrCreate(
            ['email' => 'user@travel.com'],
            [
                'name' => 'Usuário de Teste',
                'email' => 'user@travel.com',
                'password' => Hash::make('user123'),
                'role' => UserRoleEnum::USER,
                'email_verified_at' => now(),
            ]
        );

        // Criar mais alguns usuários comuns para testes
        User::firstOrCreate(
            ['email' => 'joao@travel.com'],
            [
                'name' => 'João Silva',
                'email' => 'joao@travel.com',
                'password' => Hash::make('password'),
                'role' => UserRoleEnum::USER,
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'maria@travel.com'],
            [
                'name' => 'Maria Santos',
                'email' => 'maria@travel.com',
                'password' => Hash::make('password'),
                'role' => UserRoleEnum::USER,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Usuários de demonstração criados com sucesso!');
        $this->command->info('Admin: admin@travel.com / admin123');
        $this->command->info('User: user@travel.com / user123');
        $this->command->info('Outros usuários: joao@travel.com, maria@travel.com / password');
    }
}