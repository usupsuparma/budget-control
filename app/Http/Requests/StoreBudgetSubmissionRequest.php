<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'division_id' => ['required', 'exists:division,id'],
            'submission_date' => ['required', 'date'],
            'type' => ['required', 'in:add,relocation'],
            'work_plan_id' => ['required', 'exists:kpi_workplans,id'],
            'budget_account_id' => ['required'], // Not exists:budget_account,id because it's a dynamic query sometimes? The original was just required.
            'estimation_amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ];
    }
}
