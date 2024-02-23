<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'deadline', 'priority', 'is_completed'];

    public static $priorities = [
        'normal' => 'Normal',
        'important' => 'Important',
        'very_important' => 'Very Important',
    ];  

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
