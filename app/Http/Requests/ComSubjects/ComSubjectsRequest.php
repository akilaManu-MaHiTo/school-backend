<?php

namespace App\Http\Requests\ComSubjects;

use Illuminate\Foundation\Http\FormRequest;

class ComSubjectsRequest extends FormRequest
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
            //
            'subjectCode' => 'nullable|string',
            'subjectName' => 'required|string',
            'isBasketSubject' => 'required|boolean',
            'subjectMedium' => 'required|string',
            'basketGroup' => 'enum|nullable',
        ];
    }
}
