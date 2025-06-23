<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GovernmentLink extends Model
{
    protected $fillable = [
        'name',
        'url',
        'category',
        'description',
        'active',
        'sort_order'
    ];
}
