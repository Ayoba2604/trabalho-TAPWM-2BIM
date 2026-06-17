<?php

declare(strict_types=1);

use App\Core\Blueprint;
use App\Core\MigrationInterface;
use App\Core\Schema;
use App\Models\Tarefa;

return new class implements MigrationInterface {
    public function up(Schema $schema): void
    {
        $schema->create('tarefas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('projeto_id');
            $table->string('titulo', 150);
            $table->enum('status', [
                Tarefa::STATUS_PENDENTE,
                Tarefa::STATUS_EM_ANDAMENTO,
                Tarefa::STATUS_CONCLUIDA,
            ])->default(Tarefa::STATUS_PENDENTE);
            $table->timestamps();

            $table->foreign('projeto_id')
                ->references('id')
                ->on('projetos')
                ->cascadeOnDelete();
        });
    }

    public function down(Schema $schema): void
    {
        $schema->dropIfExists('tarefas');
    }
};
