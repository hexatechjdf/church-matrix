<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChurchRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'record_unique_id',
        'organization_unique_id',
        'week_reference',
        'week_no',
        'week_volume',
        'service_date_time',
        'service_timezone',
        'value',
        'service_unique_time_id',
        'event_unique_id',
        'campus_unique_id',
        'category_unique_id',
        'record_created_at',
        'record_updated_at',
    ];
}
