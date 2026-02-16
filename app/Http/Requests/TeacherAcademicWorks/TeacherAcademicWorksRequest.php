<?php

namespace App\Http\Requests\TeacherAcademicWorks;

use Illuminate\Foundation\Http\FormRequest;

class TeacherAcademicWorksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacherId' => ['required', 'integer', 'exists:users,id'],
            'subjectId' => ['required', 'integer', 'exists:com_subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'academicWork' => ['required', 'string', 'max:255'],
            'date' => ['required', 'string', 'max:255'],
            'time' => ['required', 'string', 'max:255'],
            'approved' => ['boolean'],
        ];
    }
}
