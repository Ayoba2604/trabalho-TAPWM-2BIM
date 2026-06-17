<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap/app.php';

use App\Core\MigrationRunner;

(new MigrationRunner())->migrate(BASE_PATH . '/database/migrations');
