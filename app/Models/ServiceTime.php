<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceTime extends Model
{
    use HasFactory;

     protected $fillable = [
        'cm_id',
        'campus_id',
        'day_of_week',
        'time_of_day',
        'timezone',
        'relation_to_sunday',
        'date_start',
        'date_end',
        'replaces',
        'event_id',
        'event_name',
        'campus_name',
    ];

    public function event()
    {
        return $this->belongsTo(ChurchEvent::class, 'event_id');
    }


}
