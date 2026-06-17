<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap/app.php';

use App\Core\MigrationRunner;

(new MigrationRunner())->fresh(BASE_PATH . '/database/migrations');
