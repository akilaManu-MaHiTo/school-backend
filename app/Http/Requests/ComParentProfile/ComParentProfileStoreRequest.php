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
            'parentId' => ['nullable', 'integer','exists:users,id'],
            'studentId' => ['required', 'integer','exists:users,id'],
        ];
    }
}
