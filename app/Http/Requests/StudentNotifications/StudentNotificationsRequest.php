<?php

namespace App\Http\Requests\StudentNotifications;

use Illuminate\Foundation\Http\FormRequest;

class StudentNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['required', 'string', 'max:255'],
            'year'          => ['required', 'string', 'max:255'],
            'gradeId'       => ['required', 'integer', 'exists:com_grades,id'],
            'classId'       => ['required', 'integer', 'exists:com_class_mngs,id'],
            'ignoreUserIds' => ['nullable', 'array'],
            'ignoreUserIds.*' => ['integer', 'exists:users,id'],
        ];
    }
}
