<?php
namespace App\Http\Requests\ComPermission;

use Illuminate\Foundation\Http\FormRequest;

class ComPermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            'userType'         => 'required|string|unique:com_permissions,userType,'.$this->route('id'),
            'description'      => 'nullable|string',
            'permissionObject' => 'required|array',
        ];
    }
}
