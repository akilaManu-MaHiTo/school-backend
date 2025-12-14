<?php

namespace App\Http\Requests\StudentMarks;

use Illuminate\Foundation\Http\FormRequest;

class StudentMarksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'studentProfileId'  => ['required', 'integer', 'exists:com_student_profiles,id'],
            'academicSubjectId' => ['required', 'integer', 'exists:com_subjects,id'],
            'studentMark'       => ['nullable', 'string', 'max:255'],
            'markGrade'         => ['nullable', 'string', 'max:255'],
            'academicYear'      => ['nullable', 'string', 'max:255'],
            'academicTerm'      => ['nullable', 'string', 'max:255'],
            'isAbsentStudent'   => ['required', 'boolean'],
        ];
    }
}
