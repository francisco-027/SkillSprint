<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedMaterial extends Model
{
    protected $table = 'saved_materials';

    protected $fillable = [
        'user_id', 'upload_id', 'viewed',
    ];

    protected function casts(): array
    {
        return [
            'viewed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function upload(): BelongsTo
    {
        return $this->belongsTo(Upload::class);
    }
}
