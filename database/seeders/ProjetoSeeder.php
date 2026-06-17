<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Projeto;

final class ProjetoSeeder
{
    public function run(): array
    {
        $now = date('Y-m-d H:i:s');

        return [
            Projeto::create([
                'nome' => 'Portal do Colaborador',
                'descricao' => 'Sistema para centralizar comunicados, documentos e solicitacoes internas.',
                'data_inicio' => '2026-02-03',
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            Projeto::create([
                'nome' => 'Controle de Estoque',
                'descricao' => 'Modulo para acompanhar entradas, saidas e alertas de reposicao.',
                'data_inicio' => '2026-03-12',
                'created_at' => $now,
                'updated_at' => $now,
            ]),
            Projeto::create([
                'nome' => 'Dashboard Financeiro',
                'descricao' => 'Painel gerencial para visualizar receitas, despesas e indicadores mensais.',
                'data_inicio' => '2026-04-21',
                'created_at' => $now,
                'updated_at' => $now,
            ]),
        ];
    }
}
