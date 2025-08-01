<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{

    public function authorize(): bool
    {
        return false;
    }

    public function rules(): array
    {
        return [
            'slots' => 'required|array|min:1',
            'slots.*.start_time' => 'required|date_format:Y-m-d H:i:s',
            'slots.*.end_time' => 'required|date_format:Y-m-d H:i:s|after:slots.*.start_time',
        ];
    }
}
