<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmToken extends Model
{
    use HasFactory;
    protected $guarded = [];

    // public function urefresh(): bool
    // {
    //     $is_refresh = false;
    //     try {
    //             list($is_refresh, $token) = CRM::getRefreshToken($this->user_id, $this, true);
    //     } catch (\Exception $e) {
    //         return 500;
    //     }
    //     return $is_refresh;
    // }
}
