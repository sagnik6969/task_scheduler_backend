<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static $priorities = [
        'normal' => 'Normal',
        'important' => 'Important',
        'very_important' => 'Very Important',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeTimeFilter(Builder $query, $time = null)
    {
        if (!$time)
            return $query;
        return $query->where(
            fn(Builder $query) =>
            $query
                ->where('created_at', '>=', $time)
                ->orWhere('deadline', '>=', $time)
        );
    }
}
