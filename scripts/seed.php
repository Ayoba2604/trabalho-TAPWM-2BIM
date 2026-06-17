<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap/app.php';
require BASE_PATH . '/database/seeders/ProjetoSeeder.php';
require BASE_PATH . '/database/seeders/TarefaSeeder.php';
require BASE_PATH . '/database/seeders/DatabaseSeeder.php';

use Database\Seeders\DatabaseSeeder;

(new DatabaseSeeder())->run();

echo 'Seeders executados: 3 projetos e 12 tarefas criados.' . PHP_EOL;
