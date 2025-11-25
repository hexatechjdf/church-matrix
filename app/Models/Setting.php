<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $fillable = ['key', 'value','location_id'];
    
    public function location(){
        return $this->belongsTo(User::class,'location_id');
    }
}
