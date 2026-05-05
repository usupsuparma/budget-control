<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKPIDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year' => ['required', 'integer'],
            'kpi_division_id' => ['required', 'exists:kpi_division,id'],
            'department_id' => ['required', 'exists:department,id'],

            'department_goals' => ['required', 'string'],
            'department_activities' => ['nullable', 'string'],
            'target_department' => ['nullable', 'string'],
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
