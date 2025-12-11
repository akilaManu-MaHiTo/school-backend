<?php

namespace App\Http\Requests\ComStudentProfile;

use App\Models\ComStudentProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class ComStudentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'studentId'       => ['nullable', 'integer', 'exists:users,id'],
            'academicGradeId' => ['required', 'integer', 'exists:com_grades,id'],
            'academicClassId' => ['required', 'integer', 'exists:com_class_mngs,id'],
            'academicYear'    => ['required', 'string', 'max:255'],
            'academicMedium'  => ['required', 'string', 'max:255'],
        ];
    }
}
