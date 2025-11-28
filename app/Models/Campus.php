<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campus extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','location_id','campus_unique_id','name','region_id','description','timezone','record_fetched'];
}
