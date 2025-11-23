<?php
namespace App\Http\Requests\JobPosition;

use Illuminate\Foundation\Http\FormRequest;

class JobPositionRequest extends FormRequest
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
            'jobPosition' => 'required|string',
        ];
    }
    public function messages()
    {
        return [
            'jobPosition.required' => 'Job position is required',
        ];
    }
}
