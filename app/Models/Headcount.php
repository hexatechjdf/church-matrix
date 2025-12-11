<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Headcount extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
