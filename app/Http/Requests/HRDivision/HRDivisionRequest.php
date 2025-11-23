<?php
namespace App\Http\Requests\HRDivision;

use Illuminate\Foundation\Http\FormRequest;

class HRDivisionRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'divisionName' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'divisionName.required' => 'Division name is required',
        ];
    }
}
