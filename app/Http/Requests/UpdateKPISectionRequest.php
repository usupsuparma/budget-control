<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKPISectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'year' => ['required', 'integer'],
            'kpi_department_id' => ['required', 'exists:kpi_department,id'],
            'section_id' => ['required', 'exists:section,id'],

            'section_goals' => ['required', 'string'],
            'activities' => ['nullable', 'string'],
            'target_section' => ['nullable', 'string'],
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
            'unit_id' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ];
    }
}
