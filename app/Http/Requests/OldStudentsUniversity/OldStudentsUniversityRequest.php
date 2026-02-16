<?php

namespace App\Http\Requests\OldStudentsUniversity;

use Illuminate\Foundation\Http\FormRequest;

class OldStudentsUniversityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'studentId'        => ['required', 'integer', 'exists:users,id'],
            'universityName'   => ['required', 'string', 'max:255'],
            'country'          => ['required', 'string', 'max:255'],
            'city'             => ['required', 'string', 'max:255'],
            'degree'           => ['required', 'string', 'max:255'],
            'faculty'          => ['required', 'string', 'max:255'],
            'yearOfAdmission'  => ['required', 'string', 'max:255'],
            'yearOfGraduation' => ['required', 'string', 'max:255'],
        ];
    }
}
