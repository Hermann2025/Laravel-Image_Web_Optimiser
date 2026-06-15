<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OptimizedImage extends Model
{
    protected $fillable = [
        'session_id',
        'original_name',
        'original_size',
        'optimized_size',
        'compression_ratio',
        'mime_type',
        'format_converted_to',
        'variants',
        'path_original',
        'path_optimized',
        'status',
        'downloaded',
        'expires_at',
    ];

    protected $casts = [
        'variants' => 'array',
        'downloaded' => 'boolean',
        'expires_at' => 'datetime',
        'original_size' => 'integer',
        'optimized_size' => 'integer',
        'compression_ratio' => 'float',
    ];

    public function getGainAttribute(): float
    {
        if (!$this->original_size || !$this->optimized_size) {
            return 0;
        }
        return round((1 - ($this->optimized_size / $this->original_size)) * 100, 1);
    }

    public function scopeBySession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }
}