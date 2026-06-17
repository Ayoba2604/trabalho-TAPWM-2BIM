<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap/app.php';

use App\Core\Database;
use App\Models\Projeto;
use App\Models\Tarefa;

$statuses = [
    Tarefa::STATUS_PENDENTE,
    Tarefa::STATUS_EM_ANDAMENTO,
    Tarefa::STATUS_CONCLUIDA,
];

$errors = [];
$old = [
    'nome' => '',
    'descricao' => '',
    'data_inicio' => date('Y-m-d'),
    'tarefas' => [
        ['titulo' => '', 'status' => Tarefa::STATUS_PENDENTE],
        ['titulo' => '', 'status' => Tarefa::STATUS_PENDENTE],
        ['titulo' => '', 'status' => Tarefa::STATUS_PENDENTE],
        ['titulo' => '', 'status' => Tarefa::STATUS_PENDENTE],
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['nome'] = trim((string) ($_POST['nome'] ?? ''));
    $old['descricao'] = trim((string) ($_POST['descricao'] ?? ''));
    $old['data_inicio'] = trim((string) ($_POST['data_inicio'] ?? ''));
    $old['tarefas'] = normalizeTasks($_POST['tarefas'] ?? [], $statuses);

    if ($old['nome'] === '') {
        $errors[] = 'Informe o nome do projeto.';
    }

    if ($old['data_inicio'] === '' || !isValidDate($old['data_inicio'])) {
        $errors[] = 'Informe uma data de inicio valida.';
    }

    $filledTasks = array_values(array_filter(
        $old['tarefas'],
        fn (array $tarefa): bool => trim($tarefa['titulo']) !== ''
    ));

    if ($filledTasks === []) {
        $errors[] = 'Informe pelo menos uma tarefa.';
    }

    if ($errors === []) {
        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            $now = date('Y-m-d H:i:s');
            $projeto = Projeto::create([
                'nome' => $old['nome'],
                'descricao' => $old['descricao'],
                'data_inicio' => $old['data_inicio'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($filledTasks as $tarefa) {
                Tarefa::create([
                    'projeto_id' => $projeto->id,
                    'titulo' => trim($tarefa['titulo']),
                    'status' => $tarefa['status'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $pdo->commit();
            header('Location: index.php?created=1');
            exit;
        } catch (Throwable $exception) {
            $pdo->rollBack();
            $errors[] = 'Nao foi possivel salvar o kanban. Verifique se o banco esta criado e tente novamente.';
        }
    }
}

function normalizeTasks(array $tasks, array $statuses): array
{
    $normalized = [];

    for ($index = 0; $index < 4; $index++) {
        $task = $tasks[$index] ?? [];
        $status = (string) ($task['status'] ?? Tarefa::STATUS_PENDENTE);

        $normalized[] = [
            'titulo' => trim((string) ($task['titulo'] ?? '')),
            'status' => in_array($status, $statuses, true) ? $status : Tarefa::STATUS_PENDENTE,
        ];
    }

    return $normalized;
}

function isValidDate(string $date): bool
{
    $parsed = DateTimeImmutable::createFromFormat('Y-m-d', $date);

    return $parsed !== false && $parsed->format('Y-m-d') === $date;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Novo Kanban</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f5f7fb;
            --panel: #ffffff;
            --text: #202736;
            --muted: #667085;
            --line: #d8dee9;
            --accent: #0f766e;
            --danger: #b42318;
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
            width: min(920px, calc(100% - 32px));
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
            font-size: clamp(28px, 4vw, 40px);
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

        form {
            display: grid;
            gap: 18px;
            padding: 20px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
        }

        fieldset {
            display: grid;
            gap: 14px;
            margin: 0;
            padding: 0;
            border: 0;
        }

        legend {
            margin-bottom: 4px;
            font-size: 18px;
            font-weight: 700;
        }

        label {
            display: grid;
            gap: 7px;
            color: var(--text);
            font-weight: 700;
        }

        input,
        textarea,
        select {
            width: 100%;
            min-height: 40px;
            padding: 9px 10px;
            border: 1px solid var(--line);
            border-radius: 6px;
            color: var(--text);
            font: inherit;
            background: #ffffff;
        }

        textarea {
            min-height: 96px;
            resize: vertical;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 190px;
            gap: 14px;
        }

        .task-row {
            display: grid;
            grid-template-columns: 1fr 180px;
            gap: 10px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 6px;
            background: #fbfcfe;
        }

        .errors {
            display: grid;
            gap: 6px;
            margin: 0;
            padding: 12px 14px 12px 32px;
            border: 1px solid #f4b4ae;
            border-radius: 6px;
            background: #fff1f0;
            color: var(--danger);
            font-weight: 700;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            flex-wrap: wrap;
            border-top: 1px solid var(--line);
            padding-top: 18px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 9px 15px;
            border: 1px solid var(--accent);
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
        }

        .button-primary {
            background: var(--accent);
            color: #ffffff;
        }

        .button-secondary {
            background: #ffffff;
            color: var(--accent);
        }

        @media (max-width: 760px) {
            header,
            .grid,
            .task-row {
                grid-template-columns: 1fr;
            }

            header {
                display: grid;
                align-items: start;
            }
        }
    </style>
</head>
<body>
<main>
    <header>
        <div>
            <h1>Novo Kanban</h1>
            <p>Cadastre um projeto e suas tarefas iniciais.</p>
        </div>
        <a href="index.php">Voltar para lista</a>
    </header>

    <form method="post" action="create.php">
        <?php if ($errors !== []): ?>
            <ul class="errors">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <fieldset>
            <legend>Dados do projeto</legend>

            <div class="grid">
                <label>
                    Nome
                    <input name="nome" maxlength="120" required value="<?= e($old['nome']) ?>">
                </label>

                <label>
                    Data de inicio
                    <input type="date" name="data_inicio" required value="<?= e($old['data_inicio']) ?>">
                </label>
            </div>

            <label>
                Descricao
                <textarea name="descricao"><?= e($old['descricao']) ?></textarea>
            </label>
        </fieldset>

        <fieldset>
            <legend>Tarefas iniciais</legend>

            <?php foreach ($old['tarefas'] as $index => $tarefa): ?>
                <div class="task-row">
                    <label>
                        Tarefa <?= $index + 1 ?>
                        <input name="tarefas[<?= $index ?>][titulo]" maxlength="150" value="<?= e($tarefa['titulo']) ?>">
                    </label>

                    <label>
                        Status
                        <select name="tarefas[<?= $index ?>][status]">
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?= e($status) ?>" <?= $tarefa['status'] === $status ? 'selected' : '' ?>>
                                    <?= e($status) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
            <?php endforeach; ?>
        </fieldset>

        <div class="actions">
            <a class="button button-secondary" href="index.php">Cancelar</a>
            <button class="button button-primary" type="submit">Salvar Kanban</button>
        </div>
    </form>
</main>
</body>
</html>
