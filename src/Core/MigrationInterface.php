<?php

declare(strict_types=1);

namespace App\Core;

interface MigrationInterface
{
    public function up(Schema $schema): void;

    public function down(Schema $schema): void;
}
