<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChurchEvent extends Model
{
    use HasFactory;


    protected $fillable = ['name', 'cm_id'];

    public function serviceTimes()
    {
        return $this->hasMany(ServiceTime::class, 'event_id');
    }

}
