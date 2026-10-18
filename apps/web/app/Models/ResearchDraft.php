<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class ResearchDraft extends Model
{
    protected $fillable = [
        'public_id',
        'access_token_hash',
        'current_revision',
    ];

    public function revisions(): HasMany
    {
        return $this->hasMany(ResearchDraftRevision::class);
    }

    public function currentRevision(): HasOne
    {
        return $this->hasOne(ResearchDraftRevision::class)->where('revision', $this->current_revision);
    }
}
