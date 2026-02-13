<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;
    protected $table = 'subjects';
    protected $fillable = [
        'name', 'code'
    ];
    public function sheets(): HasMany
    {
        return $this->hasMany(Sheet::class);
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'code' => 'string',
        ];
    }

    /**
     * Set the subject's code to uppercase.
     */
    protected function setCodeAttribute(string $value): void
    {
        $this->attributes['code'] = strtoupper($value);
    }
}
