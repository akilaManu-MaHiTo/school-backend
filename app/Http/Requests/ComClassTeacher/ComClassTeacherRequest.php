<?php

namespace App\Http\Requests\ComClassTeacher;

use Illuminate\Foundation\Http\FormRequest;

class ComClassTeacherRequest extends FormRequest
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
            'classId' => 'nullable|integer|exists:com_class_mngs,id',
            'teacherId' => 'nullable|integer|exists:users,id',
            'gradeId' => 'nullable|integer|max:10',
            'year' => 'nullable|string|max:10',
        ];
    }
}
