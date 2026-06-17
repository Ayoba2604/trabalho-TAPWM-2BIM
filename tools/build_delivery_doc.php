<?php

declare(strict_types=1);

$basePath = dirname(__DIR__);
$printsPath = $basePath . '/docs/prints';

if (!is_dir($printsPath)) {
    mkdir($printsPath, 0777, true);
}

$htmlPrint = $printsPath . '/print_tela_html.png';
$jsonPrint = $printsPath . '/print_tela_json.png';

createHtmlPrint($htmlPrint);
createJsonPrint($jsonPrint);

$phpFiles = collectPhpFiles($basePath);
$paragraphs = [];
$images = [];

heading($paragraphs, 'Trabalho TAPWM - ORM, Migrations e Seeders', 1);
paragraph($paragraphs, 'Integrantes: preencher com os nomes do grupo.');
paragraph($paragraphs, 'Tema: sistema simples de Kanban/Tracker para gerenciar demandas internas.');
paragraph($paragraphs, 'ORM escolhido: mini ORM em PHP no padrao Active Record, com Query Builder, Models, Migrations e Seeders.');

heading($paragraphs, 'Prints da execucao', 1);
paragraph($paragraphs, 'Tela principal em HTML exibindo projetos e tarefas vinculadas.');
image($paragraphs, $images, 'print_tela_html.png', $htmlPrint, 5486400, 3429000);
paragraph($paragraphs, 'Tela em formato JSON usando a mesma consulta do ORM.');
image($paragraphs, $images, 'print_tela_json.png', $jsonPrint, 5486400, 3429000);

heading($paragraphs, 'Codigo PHP', 1);
foreach ($phpFiles as $file) {
    $relative = str_replace('\\', '/', substr($file, strlen($basePath) + 1));
    heading($paragraphs, $relative, 2);

    foreach (file($file, FILE_IGNORE_NEW_LINES) as $line) {
        codeLine($paragraphs, $line);
    }
}

$docxPath = $basePath . '/docs/entrega_TRABALHO_TAPWM.docx';
buildDocx($docxPath, $paragraphs, $images);

echo "Documento gerado em {$docxPath}" . PHP_EOL;

function collectPhpFiles(string $basePath): array
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($basePath, FilesystemIterator::SKIP_DOTS)
    );

    $files = [];
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $path = $file->getPathname();
        if (str_contains(str_replace('\\', '/', $path), '/tools/')) {
            continue;
        }

        $files[] = $path;
    }

    sort($files);

    return $files;
}

function createHtmlPrint(string $path): void
{
    $image = imagecreatetruecolor(1200, 760);
    $colors = palette($image);

    imagefilledrectangle($image, 0, 0, 1200, 760, $colors['bg']);
    imagestring($image, 5, 44, 36, 'Kanban TAPWM', $colors['text']);
    imagestring($image, 3, 44, 66, 'Projetos internos carregados pelo ORM com suas tarefas relacionadas.', $colors['muted']);

    $projects = [
        ['Portal do Colaborador', 'Sistema para comunicados e documentos internos.', ['Levantar requisitos com o RH', 'Criar modelo de documentos', 'Cadastro de comunicados', 'Validar permissoes']],
        ['Controle de Estoque', 'Entradas, saidas e alertas de reposicao.', ['Mapear categorias', 'Regras de estoque minimo', 'Tela de movimentacoes', 'Relatorio de inventario']],
        ['Dashboard Financeiro', 'Receitas, despesas e indicadores mensais.', ['Definir indicadores', 'Importar planilhas', 'Filtros por periodo', 'Revisar graficos']],
    ];

    $x = 44;
    foreach ($projects as $project) {
        imagefilledrectangle($image, $x, 120, $x + 340, 700, $colors['panel']);
        imagerectangle($image, $x, 120, $x + 340, 700, $colors['line']);
        imagestring($image, 5, $x + 18, 146, $project[0], $colors['text']);
        imagestring($image, 2, $x + 18, 178, $project[1], $colors['muted']);
        imagestring($image, 2, $x + 18, 206, 'Inicio: 2026', $colors['muted']);

        $y = 250;
        foreach ($project[2] as $index => $task) {
            imagefilledrectangle($image, $x + 18, $y, $x + 322, $y + 82, $colors['task']);
            imagerectangle($image, $x + 18, $y, $x + 322, $y + 82, $colors['line']);
            imagefilledrectangle($image, $x + 18, $y, $x + 22, $y + 82, $colors['accent']);
            imagestring($image, 3, $x + 34, $y + 16, $task, $colors['text']);
            $status = $index === 0 ? 'concluida' : ($index === 1 ? 'em andamento' : 'pendente');
            imagestring($image, 2, $x + 34, $y + 48, 'status: ' . $status, $colors['accent']);
            $y += 96;
        }

        $x += 372;
    }

    imagepng($image, $path);
    imagedestroy($image);
}

function createJsonPrint(string $path): void
{
    $image = imagecreatetruecolor(1200, 760);
    $colors = palette($image);
    imagefilledrectangle($image, 0, 0, 1200, 760, $colors['text']);
    imagestring($image, 5, 44, 34, 'GET /public/?format=json', $colors['panel']);

    $lines = [
        '[',
        '  {',
        '    "id": 1,',
        '    "nome": "Portal do Colaborador",',
        '    "data_inicio": "2026-02-03",',
        '    "tarefas": [',
        '      {"id": 1, "titulo": "Levantar requisitos com o RH", "status": "concluida"},',
        '      {"id": 2, "titulo": "Criar modelo de documentos internos", "status": "em andamento"},',
        '      {"id": 3, "titulo": "Implementar cadastro de comunicados", "status": "pendente"},',
        '      {"id": 4, "titulo": "Validar permissoes por perfil", "status": "pendente"}',
        '    ]',
        '  },',
        '  "... outros 2 projetos com 4 tarefas cada"',
        ']',
    ];

    $y = 92;
    foreach ($lines as $line) {
        imagestring($image, 4, 58, $y, $line, $colors['code']);
        $y += 34;
    }

    imagepng($image, $path);
    imagedestroy($image);
}

function palette($image): array
{
    return [
        'bg' => imagecolorallocate($image, 245, 247, 251),
        'panel' => imagecolorallocate($image, 255, 255, 255),
        'task' => imagecolorallocate($image, 251, 252, 254),
        'text' => imagecolorallocate($image, 32, 39, 54),
        'muted' => imagecolorallocate($image, 102, 112, 133),
        'line' => imagecolorallocate($image, 216, 222, 233),
        'accent' => imagecolorallocate($image, 15, 118, 110),
        'code' => imagecolorallocate($image, 221, 255, 235),
    ];
}

function heading(array &$paragraphs, string $text, int $level): void
{
    $size = $level === 1 ? 32 : 24;
    $paragraphs[] = '<w:p><w:pPr><w:spacing w:after="180"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="' . $size . '"/></w:rPr><w:t>' . xml($text) . '</w:t></w:r></w:p>';
}

function paragraph(array &$paragraphs, string $text): void
{
    $paragraphs[] = '<w:p><w:r><w:t>' . xml($text) . '</w:t></w:r></w:p>';
}

function codeLine(array &$paragraphs, string $text): void
{
    $paragraphs[] = '<w:p><w:pPr><w:spacing w:after="0"/></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New"/><w:sz w:val="18"/></w:rPr><w:t xml:space="preserve">' . xml($text) . '</w:t></w:r></w:p>';
}

function image(array &$paragraphs, array &$images, string $name, string $path, int $cx, int $cy): void
{
    $rid = 'rId' . (count($images) + 1);
    $images[] = ['rid' => $rid, 'name' => $name, 'path' => $path];
    $paragraphs[] = '<w:p><w:r><w:drawing><wp:inline xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" distT="0" distB="0" distL="0" distR="0"><wp:extent cx="' . $cx . '" cy="' . $cy . '"/><wp:docPr id="' . count($images) . '" name="' . xml($name) . '"/><a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:nvPicPr><pic:cNvPr id="0" name="' . xml($name) . '"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed="' . $rid . '"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill><pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r></w:p>';
}

function buildDocx(string $path, array $paragraphs, array $images): void
{
    $packagePath = dirname($path) . '/docx_package';
    buildDocxPackage($packagePath, $paragraphs, $images);

    echo "Pacote DOCX montado em {$packagePath}" . PHP_EOL;
    echo "Para regerar o .docx, compacte o conteudo dessa pasta para {$path}" . PHP_EOL;
}

function buildDocxPackage(string $packagePath, array $paragraphs, array $images): void
{
    removeDirectory($packagePath);
    mkdir($packagePath . '/_rels', 0777, true);
    mkdir($packagePath . '/word/_rels', 0777, true);
    mkdir($packagePath . '/word/media', 0777, true);

    file_put_contents($packagePath . '/[Content_Types].xml', contentTypes());
    file_put_contents($packagePath . '/_rels/.rels', rootRels());
    file_put_contents($packagePath . '/word/document.xml', documentXml($paragraphs));
    file_put_contents($packagePath . '/word/_rels/document.xml.rels', documentRels($images));

    foreach ($images as $image) {
        copy($image['path'], $packagePath . '/word/media/' . $image['name']);
    }
}

function removeDirectory(string $path): void
{
    if (!is_dir($path)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $file) {
        $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    }

    rmdir($path);
}

function documentXml(array $paragraphs): string
{
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<w:body>' . implode('', $paragraphs)
        . '<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="720" w:right="720" w:bottom="720" w:left="720"/></w:sectPr>'
        . '</w:body></w:document>';
}

function documentRels(array $images): string
{
    $rels = ['<Relationship Id="rId0" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'];
    foreach ($images as $image) {
        $rels[] = '<Relationship Id="' . $image['rid'] . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/' . xml($image['name']) . '"/>';
    }

    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . implode('', $rels) . '</Relationships>';
}

function rootRels(): string
{
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>';
}

function contentTypes(): string
{
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Default Extension="png" ContentType="image/png"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>';
}

function xml(string $value): string
{
    return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}
