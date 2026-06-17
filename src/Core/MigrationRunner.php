<?php

declare(strict_types=1);

namespace App\Core;

final class MigrationRunner
{
    public function __construct(
        private readonly Schema $schema = new Schema()
    ) {
    }

    public function migrate(string $path): void
    {
        $this->ensureMigrationsTable();
        $applied = $this->appliedMigrations();

        foreach ($this->migrationFiles($path) as $file) {
            $name = basename($file);

            if (in_array($name, $applied, true)) {
                echo "Ignorada: {$name} ja foi executada." . PHP_EOL;
                continue;
            }

            $migration = require $file;
            $migration->up($this->schema);

            Database::statement(
                'INSERT INTO ' . Database::quoteIdentifier('migrations') . ' (' . Database::quoteIdentifier('migration') . ', ' . Database::quoteIdentifier('executed_at') . ') VALUES (:migration, :executed_at)',
                ['migration' => $name, 'executed_at' => date('Y-m-d H:i:s')]
            );

            echo "Executada: {$name}" . PHP_EOL;
        }
    }

    public function fresh(string $path): void
    {
        $files = array_reverse($this->migrationFiles($path));

        foreach ($files as $file) {
            $migration = require $file;
            $migration->down($this->schema);
        }

        $this->schema->dropIfExists('migrations');
        echo 'Banco limpo. Recriando tabelas...' . PHP_EOL;

        $this->migrate($path);
    }

    private function ensureMigrationsTable(): void
    {
        $this->schema->create('migrations', function (Blueprint $table): void {
            $table->id();
            $table->string('migration', 180);
            $table->string('executed_at', 30);
        });
    }

    private function appliedMigrations(): array
    {
        return array_column(
            Database::select('SELECT ' . Database::quoteIdentifier('migration') . ' FROM ' . Database::quoteIdentifier('migrations')),
            'migration'
        );
    }

    private function migrationFiles(string $path): array
    {
        $files = glob(rtrim($path, '/\\') . '/*.php') ?: [];
        sort($files);

        return $files;
    }
}
