<?php

namespace App\Http\Requests\ComParentProfile;

use Illuminate\Foundation\Http\FormRequest;

class ComParentProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parentId' => ['sometimes', 'integer', 'exists:users,id'],
            'studentProfileId' => ['sometimes', 'integer', 'exists:com_student_profiles,id'],
        ];
    }
}
