<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InlineUpdateKPIDivisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'field' => ['required', 'string', Rule::in($this->allowedFields())],
            'value' => ['nullable'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->sometimes('value', ['integer'], function ($input) {
            return in_array($input->field, ['year', 'duration_days'], true);
        });

        $validator->sometimes('value', ['date'], function ($input) {
            return in_array($input->field, ['schedule_start', 'schedule_end'], true);
        });

        $validator->sometimes('value', ['boolean'], function ($input) {
            return in_array($input->field, $this->monthFields(), true);
        });
    }

    private function allowedFields(): array
    {
        return [
            'year',
            'division_goals',
            'target_division',
            'duration_days',
            'schedule_start',
            'schedule_end',
            'jan',
            'feb',
            'mar',
            'apr',
            'may',
            'jun',
            'jul',
            'aug',
            'sep',
            'oct',
            'nov',
            'dec',
            'revenue_cost',
            'pic',
            'description',
        ];
    }

    private function monthFields(): array
    {
        return ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
    }
}
