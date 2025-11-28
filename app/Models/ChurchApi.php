<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChurchApi extends Model
{
    use HasFactory;

    protected $table = 'churchapi';

    protected $fillable = ['church_matrix_api', 'church_matrix_user', 'location_id', 'select_region'];
}
