<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'           => 'nullable|string',
            'mobile'         => 'nullable|string',
            'gender'         => 'nullable|string',
            'removeDoc'      => 'nullable|array',
            'profileImage'   => 'nullable|array',
            'profileImage.*' => 'file|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }

}
