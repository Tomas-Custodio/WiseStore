<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Contas bancárias da loja, associadas ao admin (pode correr mais de uma vez).
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $contas = [
            [
                'name' => 'BAI — Banco Angolano de Investimentos',
                'number' => '123456789 10 001',
                'iban' => 'AO06 0040 0000 1234 5678 9101 2',
            ],
            [
                'name' => 'BFA — Banco de Fomento Angola',
                'number' => '987654321 30 001',
                'iban' => 'AO06 0006 0000 9876 5432 1301 5',
            ],
        ];

        foreach ($contas as $conta) {
            $admin->bancos()->updateOrCreate(
                ['iban' => $conta['iban']],
                $conta,
            );
        }
    }
}
