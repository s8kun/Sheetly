<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sheet extends Model
{
    use HasFactory;
    protected $table = 'sheets';
    protected $fillable = ['title', 'user_id', 'subject_id',
        'chapter_number', 'file_url', 'downloads_count', 'type', 'status'];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
