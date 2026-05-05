<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKPIDivisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year' => ['required', 'integer'],
            'company_policy_detail_id' => ['required', 'exists:company_policy_detail,id'],
            'division_id' => ['required', 'exists:division,id'],

            'division_goals' => ['required', 'string'],
            'target_division' => ['nullable', 'string'],
            'duration_days' => ['nullable', 'integer'],
            'schedule_start' => ['nullable', 'date'],
            'schedule_end' => ['nullable', 'date'],

            'jan' => ['nullable', 'boolean'],
            'feb' => ['nullable', 'boolean'],
            'mar' => ['nullable', 'boolean'],
            'apr' => ['nullable', 'boolean'],
            'may' => ['nullable', 'boolean'],
            'jun' => ['nullable', 'boolean'],
            'jul' => ['nullable', 'boolean'],
            'aug' => ['nullable', 'boolean'],
            'sep' => ['nullable', 'boolean'],
            'oct' => ['nullable', 'boolean'],
            'nov' => ['nullable', 'boolean'],
            'dec' => ['nullable', 'boolean'],

            'revenue_cost' => ['nullable', 'string'],
            'pic' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ];
    }
}
