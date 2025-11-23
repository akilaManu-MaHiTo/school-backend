<?php
namespace App\Http\Requests\OhMiPiSupplierType;

use Illuminate\Foundation\Http\FormRequest;

class SupplierTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'typeName' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'typeName.required' => 'Supplier type name is required',
        ];
    }
}
