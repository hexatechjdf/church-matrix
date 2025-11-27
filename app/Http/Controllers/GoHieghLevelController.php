<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\CRM;

class GoHieghLevelController extends Controller
{
    public function callback(Request $request)
    {
        save_logs($request);
        // ghl_token($request->code??"");
        $code = $request->code ?? null;
        if ($code) {
            $user_id = auth()->user()->id;
            $code = CRM::crm_token($code, '');
            $code = json_decode($code);
            $user_type = $code->userType ?? null;
            $e = route('error');
            if ($user_type) {
                $token = $user->crmauth ?? null;
                list($connected, $con) = CRM::go_and_get_token($code, '', $user_id, $token);


                if (!$connected) {
                    return redirect($e)->with('error', json_encode($code));
                }

            }
            return redirect($e)->with('error', 'Not allowed to connect');
        }
    }
}
