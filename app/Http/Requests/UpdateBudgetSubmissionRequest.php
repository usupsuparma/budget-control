<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBudgetSubmissionRequest extends FormRequest
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
        $year = date('Y');
        return [
            'division_id' => ['required', 'exists:division,id'],
            'submission_date' => [
                'required', 
                'date', 
                'after_or_equal:' . $year . '-01-01', 
                'before_or_equal:' . $year . '-12-31'
            ],
            'type' => ['required', 'in:add,relocation'],
            'work_plan_id' => ['required', 'exists:kpi_workplans,id'],
            'budget_account_id' => [
                'required',
                'integer',
                Rule::exists('workplan_budget_items', 'id')->whereNull('deleted_at'),
            ],
            'source_budget_account_id' => [
                'nullable',
                'required_if:type,relocation',
                'integer',
                'different:budget_account_id',
                Rule::exists('workplan_budget_items', 'id')->whereNull('deleted_at'),
            ],
            'estimation_amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $year = date('Y');
        return [
            'submission_date.after_or_equal' => "Tanggal submission harus berada di tahun {$year}.",
            'submission_date.before_or_equal' => "Tanggal submission harus berada di tahun {$year}.",
        ];
    }
}
