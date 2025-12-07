<?php
namespace App\Http\Requests\ComTeacherProfile;

use Illuminate\Foundation\Http\FormRequest;

class ComTeacherProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacherId'         => 'nullable|integer',
            'academicGradeId'   => 'required|integer',
            'academicSubjectId' => 'required|integer',
            'academicClassId'   => 'required|integer',
            'academicYear'      => 'required|string|max:255',
            'academicMedium'    => 'required|string|max:255',
        ];
    }
}
