<?php

namespace App\Http\Requests\ComParentProfile;

use Illuminate\Foundation\Http\FormRequest;

class ComParentProfileStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parentId' => ['required', 'integer', 'exists:users,id'],
            'studentProfileId' => ['required', 'integer', 'exists:com_student_profiles,id'],
        ];
    }
}
