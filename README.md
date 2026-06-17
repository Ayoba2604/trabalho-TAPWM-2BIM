# Kanban TAPWM - ORM, Migrations e Seeders

Sistema simples de tracker/Kanban para demonstrar ORM em PHP com relacionamento 1:N entre `Projeto` e `Tarefa`.

## Estrutura

- `src/Models/Projeto.php`: model de projeto com relacionamento `tarefas()`.
- `src/Models/Tarefa.php`: model de tarefa com relacionamento `projeto()`.
- `database/migrations`: criacao das tabelas `projetos` e `tarefas`.
- `database/seeders`: dados iniciais com 3 projetos e 12 tarefas.
- `public/index.php`: consulta todos os projetos com tarefas usando ORM, sem SQL cru.
- `public/create.php`: tela para cadastrar um novo kanban com projeto e tarefas.
- `scripts`: comandos de banco para criar, migrar e popular.

## Configuracao e criacao do banco

1. Abra o Wamp e deixe o MySQL/MariaDB ligado.

No icone do Wamp, o servico precisa estar verde. Se estiver vermelho ou laranja, o PHP nao vai conseguir criar o banco.

2. Confira o arquivo `.env`.

Esse arquivo diz para o PHP qual banco criar e qual usuario usar:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tapwm_kanban
DB_USERNAME=root
DB_PASSWORD=
```

Com essa configuracao, o nome do banco sera `tapwm_kanban`.

3. Abra o PowerShell dentro da pasta do projeto:

```powershell
cd C:\wamp64\www8\galvani\TRABALHO_TAPWM
```

4. Crie o banco vazio:

```powershell
C:\wamp64\bin\php\php8.3.28\php.exe scripts/create_database.php
```

Esse comando cria no MySQL/MariaDB um banco chamado `tapwm_kanban`. Ele nao cria as tabelas ainda.

5. Crie as tabelas com as migrations:

```powershell
C:\wamp64\bin\php\php8.3.28\php.exe scripts/fresh.php
```

Esse comando cria as tabelas:

- `projetos`
- `tarefas`
- `migrations`

6. Coloque os dados de exemplo com os seeders:

```powershell
C:\wamp64\bin\php\php8.3.28\php.exe scripts/seed.php
```

Esse comando cadastra 3 projetos e 12 tarefas.

7. Abra no navegador:

```text
http://localhost/www8/galvani/TRABALHO_TAPWM/public/
```

Para visualizar em JSON:

```text
http://localhost/www8/galvani/TRABALHO_TAPWM/public/?format=json
```

Para cadastrar um novo kanban:

```text
http://localhost/www8/galvani/TRABALHO_TAPWM/public/create.php
```

## Como a consulta principal atende a regra

O arquivo `public/index.php` busca os dados assim:

```php
$projetos = Projeto::with('tarefas')
    ->orderBy('data_inicio')
    ->get();
```

Ou seja, a tela usa metodos do ORM e nao escreve SQL puro na etapa de consulta/exibicao.
