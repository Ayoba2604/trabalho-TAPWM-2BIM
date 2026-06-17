<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Projeto;
use App\Models\Tarefa;

final class DatabaseSeeder
{
    public function run(): void
    {
        Tarefa::query()->delete();
        Projeto::query()->delete();

        $projetos = (new ProjetoSeeder())->run();
        (new TarefaSeeder())->run($projetos);
    }
}
