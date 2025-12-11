<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventsData extends Model
{
    protected $table = 'events_data'; 
    protected $guarded = ['id'];  

  

    protected $casts = [
        'service_date' => 'date',
        'service_time' => 'datetime:H:i',
        'is_live' => 'boolean',
    ];
}