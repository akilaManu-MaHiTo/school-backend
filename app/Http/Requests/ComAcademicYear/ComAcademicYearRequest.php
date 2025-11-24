<?php

namespace App\Http\Requests\ComAcademicYear;

use Illuminate\Foundation\Http\FormRequest;

class ComAcademicYearRequest extends FormRequest
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
            //
            'year' => 'required|integer|unique:com_academic_years,year',
        ];
    }
}
