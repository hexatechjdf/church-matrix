<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveChurchApiRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'church_matrix_user' => 'required|email',
            'church_matrix_api'  => 'required|string',
        ];
    }
}
