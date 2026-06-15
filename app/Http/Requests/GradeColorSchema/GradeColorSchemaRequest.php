<?php

namespace App\Http\Requests\GradeColorSchema;

use Illuminate\Foundation\Http\FormRequest;

class GradeColorSchemaRequest extends FormRequest
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
        $gradeColorSchema = $this->route('gradeColorSchema');
        $gradeColorSchemaId = is_object($gradeColorSchema) ? $gradeColorSchema->id : $gradeColorSchema;

        return [
            'gradeName' => 'required|string|max:255|unique:grade_color_schemas,gradeName,'.$gradeColorSchemaId,
            'marksRange' => 'required|string|max:255',
            'color' => 'required|string|max:255',
        ];
    }
}
