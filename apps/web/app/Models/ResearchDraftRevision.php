<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ResearchDraftRevision extends Model
{
    protected $fillable = [
        'revision',
        'project_title',
        'research_question',
        'interpretation',
        'status',
        'confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'project_title' => 'encrypted',
            'research_question' => 'encrypted',
            'interpretation' => 'encrypted:array',
            'confirmed_at' => 'immutable_datetime',
        ];
    }

    public function draft(): BelongsTo
    {
        return $this->belongsTo(ResearchDraft::class);
    }
}
