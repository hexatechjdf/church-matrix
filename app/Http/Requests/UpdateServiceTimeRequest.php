<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceTimeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'campus_id' => 'required|string|max:255',
            'day_of_week' => 'required|string|max:50',
            'time_of_day' => 'required|date_format:H:i',
            'timezone' => 'required|string|max:50',
            'relation_to_sunday' => 'nullable|string|max:50',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
            'replaces' => 'nullable|string|max:255',
            'event_id' => 'required|exists:church_events,id',
        ];
    }
}
