<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $connection = 'sekolah_gateway';

    protected $fillable = [
        'name',
        'db_host',
        'port',
        'db_name',
        'db_user',
        'db_pass',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'port' => 'integer',
    ];

    public function scopeActive(Builder $query): void
    {
        $query->where('status', true);
    }
}
