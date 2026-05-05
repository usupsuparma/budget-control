<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KPIDivisionDataTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year' => ['nullable', 'integer'],
        ];
    }
}
