<?php

declare(strict_types=1);

use App\Core\Blueprint;
use App\Core\MigrationInterface;
use App\Core\Schema;

return new class implements MigrationInterface {
    public function up(Schema $schema): void
    {
        $schema->create('projetos', function (Blueprint $table): void {
            $table->id();
            $table->string('nome', 120);
            $table->text('descricao')->nullable();
            $table->date('data_inicio');
            $table->timestamps();
        });
    }

    public function down(Schema $schema): void
    {
        $schema->dropIfExists('projetos');
    }
};
