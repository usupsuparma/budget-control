<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateModulMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'modul_name' => ['required', 'string', 'max:255'],
            'menu_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
