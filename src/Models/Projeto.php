<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use App\Core\Relations\HasMany;

final class Projeto extends Model
{
    protected string $table = 'projetos';

    protected array $fillable = [
        'nome',
        'descricao',
        'data_inicio',
        'created_at',
        'updated_at',
    ];

    public function tarefas(): HasMany
    {
        return $this->hasMany(Tarefa::class, 'projeto_id');
    }
}
