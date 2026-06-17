<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Projeto;
use App\Models\Tarefa;

final class TarefaSeeder
{
    /**
     * @param Projeto[] $projetos
     */
    public function run(array $projetos): void
    {
        $now = date('Y-m-d H:i:s');

        $tarefasPorProjeto = [
            [
                ['titulo' => 'Levantar requisitos com o RH', 'status' => Tarefa::STATUS_CONCLUIDA],
                ['titulo' => 'Criar modelo de documentos internos', 'status' => Tarefa::STATUS_EM_ANDAMENTO],
                ['titulo' => 'Implementar cadastro de comunicados', 'status' => Tarefa::STATUS_PENDENTE],
                ['titulo' => 'Validar permissoes por perfil', 'status' => Tarefa::STATUS_PENDENTE],
            ],
            [
                ['titulo' => 'Mapear categorias de produtos', 'status' => Tarefa::STATUS_CONCLUIDA],
                ['titulo' => 'Definir regras de alerta de estoque minimo', 'status' => Tarefa::STATUS_EM_ANDAMENTO],
                ['titulo' => 'Criar tela de movimentacoes', 'status' => Tarefa::STATUS_PENDENTE],
                ['titulo' => 'Testar relatorio de inventario', 'status' => Tarefa::STATUS_PENDENTE],
            ],
            [
                ['titulo' => 'Definir indicadores financeiros', 'status' => Tarefa::STATUS_CONCLUIDA],
                ['titulo' => 'Importar dados de planilhas mensais', 'status' => Tarefa::STATUS_EM_ANDAMENTO],
                ['titulo' => 'Criar filtros por periodo', 'status' => Tarefa::STATUS_PENDENTE],
                ['titulo' => 'Revisar graficos com a diretoria', 'status' => Tarefa::STATUS_PENDENTE],
            ],
        ];

        foreach ($projetos as $indice => $projeto) {
            foreach ($tarefasPorProjeto[$indice] as $tarefa) {
                Tarefa::create([
                    'projeto_id' => $projeto->id,
                    'titulo' => $tarefa['titulo'],
                    'status' => $tarefa['status'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
