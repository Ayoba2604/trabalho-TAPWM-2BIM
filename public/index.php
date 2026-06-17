<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap/app.php';

use App\Models\Projeto;

$created = ($_GET['created'] ?? '') === '1';

$projetos = Projeto::with('tarefas')
    ->orderBy('data_inicio')
    ->get();

$format = $_GET['format'] ?? 'html';

if ($format === 'json') {
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(array_map(fn (Projeto $projeto): array => [
        'id' => $projeto->id,
        'nome' => $projeto->nome,
        'descricao' => $projeto->descricao,
        'data_inicio' => $projeto->data_inicio,
        'tarefas' => array_map(fn ($tarefa): array => [
            'id' => $tarefa->id,
            'titulo' => $tarefa->titulo,
            'status' => $tarefa->status,
        ], $projeto->tarefas),
    ], $projetos), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    exit;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function statusClass(string $status): string
{
    return match ($status) {
        'concluida' => 'status-done',
        'em andamento' => 'status-progress',
        default => 'status-pending',
    };
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kanban TAPWM</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f5f7fb;
            --panel: #ffffff;
            --text: #202736;
            --muted: #667085;
            --line: #d8dee9;
            --accent: #0f766e;
            --pending: #8a5a00;
            --progress: #0f5e9c;
            --done: #1f7a3a;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        main {
            width: min(1120px, calc(100% - 32px));
            margin: 0 auto;
            padding: 32px 0 48px;
        }

        header {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 24px;
            border-bottom: 1px solid var(--line);
            padding-bottom: 18px;
        }

        h1 {
            margin: 0 0 6px;
            font-size: clamp(28px, 4vw, 42px);
            line-height: 1.1;
        }

        p {
            margin: 0;
            color: var(--muted);
        }

        a {
            color: var(--accent);
            font-weight: 700;
            text-decoration: none;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .button {
            display: inline-flex;
            align-items: center;
            min-height: 38px;
            padding: 8px 13px;
            border-radius: 6px;
            background: var(--accent);
            color: #ffffff;
        }

        .notice {
            margin-bottom: 18px;
            padding: 12px 14px;
            border: 1px solid #bfe7d2;
            border-radius: 6px;
            background: #e8f8ef;
            color: #166534;
            font-weight: 700;
        }

        .board {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .project {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
        }

        .project__header {
            padding: 16px;
            border-bottom: 1px solid var(--line);
        }

        .project h2 {
            margin: 0 0 8px;
            font-size: 20px;
        }

        .date {
            display: inline-block;
            margin-top: 12px;
            color: var(--muted);
            font-size: 13px;
        }

        .tasks {
            display: grid;
            gap: 10px;
            padding: 14px;
            margin: 0;
            list-style: none;
        }

        .task {
            min-height: 84px;
            padding: 12px;
            border: 1px solid var(--line);
            border-left: 4px solid var(--accent);
            border-radius: 6px;
            background: #fbfcfe;
        }

        .task strong {
            display: block;
            margin-bottom: 10px;
            line-height: 1.25;
        }

        .status {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            background: #eef2f7;
        }

        .status-pending {
            color: var(--pending);
            background: #fff2cc;
        }

        .status-progress {
            color: var(--progress);
            background: #dceeff;
        }

        .status-done {
            color: var(--done);
            background: #dff7e7;
        }

        @media (max-width: 900px) {
            .board {
                grid-template-columns: 1fr;
            }

            header {
                align-items: start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<main>
    <header>
        <div>
            <h1>Kanban TAPWM</h1>
            <p>Projetos internos carregados pelo ORM com suas tarefas relacionadas.</p>
        </div>
        <div class="actions">
            <a class="button" href="create.php">Novo Kanban</a>
            <a href="?format=json">Ver JSON</a>
        </div>
    </header>

    <?php if ($created): ?>
        <div class="notice">Kanban cadastrado com sucesso.</div>
    <?php endif; ?>

    <section class="board" aria-label="Projetos e tarefas">
        <?php foreach ($projetos as $projeto): ?>
            <article class="project">
                <div class="project__header">
                    <h2><?= e($projeto->nome) ?></h2>
                    <p><?= e($projeto->descricao) ?></p>
                    <span class="date">Inicio: <?= e(date('d/m/Y', strtotime($projeto->data_inicio))) ?></span>
                </div>

                <ul class="tasks">
                    <?php foreach ($projeto->tarefas as $tarefa): ?>
                        <li class="task">
                            <strong><?= e($tarefa->titulo) ?></strong>
                            <span class="status <?= e(statusClass($tarefa->status)) ?>">
                                <?= e($tarefa->status) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </article>
        <?php endforeach; ?>
    </section>
</main>
</body>
</html>
