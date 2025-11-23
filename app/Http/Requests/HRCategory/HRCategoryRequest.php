<?php
namespace App\Http\Requests\HRCategory;

use Illuminate\Foundation\Http\FormRequest;

class HRCategoryRequest extends FormRequest
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
            'categoryName'    => 'required|string',
            'subCategory'     => 'required|string',
            'observationType' => 'required|string',
        ];
    }
    public function messages()
    {
        return [
            'categoryName.required'    => 'Category name is required',
            'subCategory.required'     => 'Sub category is required',
            'observationType.required' => 'Observation type is required',
        ];
    }
}
