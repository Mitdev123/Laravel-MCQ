<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionImportBatch extends Model
{
    protected $fillable = [
        'user_id',
        'file_name',
        'preview_data',
        'total_rows',
        'valid_rows',
        'invalid_rows',
        'duplicate_rows',
        'status',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'preview_data' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}