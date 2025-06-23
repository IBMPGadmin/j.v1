<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RCICDeadline extends Model
{
    protected $table = 'rcic_deadlines'; // Explicitly set the table name

    protected $fillable = [
        'title',
        'category',
        'type',
        'description',
        'days_before',
        'status'
    ];

    protected $casts = [
        'days_before' => 'integer',
    ];
}
