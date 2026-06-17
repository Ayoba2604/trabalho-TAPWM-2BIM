<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\Relations\BelongsTo;

final class Tarefa extends Model
{
    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_EM_ANDAMENTO = 'em andamento';
    public const STATUS_CONCLUIDA = 'concluida';

    protected string $table = 'tarefas';

    protected array $fillable = [
        'projeto_id',
        'titulo',
        'status',
        'created_at',
        'updated_at',
    ];

    public function projeto(): BelongsTo
    {
        return $this->belongsTo(Projeto::class, 'projeto_id');
    }
}
