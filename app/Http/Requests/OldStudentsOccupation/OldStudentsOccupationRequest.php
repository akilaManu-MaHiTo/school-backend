<?php

namespace App\Http\Requests\OldStudentsOccupation;

use Illuminate\Foundation\Http\FormRequest;

class OldStudentsOccupationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'studentId'          => ['required', 'integer', 'exists:users,id'],
            'companyName'        => ['required', 'string', 'max:255'],
            'occupation'         => ['required', 'string', 'max:255'],
            'description'        => ['nullable', 'string', 'max:1000'],
            'dateOfRegistration' => ['required', 'string', 'max:255'],
            'country'            => ['required', 'string', 'max:255'],
            'city'               => ['required', 'string', 'max:255'],
        ];
    }
}
