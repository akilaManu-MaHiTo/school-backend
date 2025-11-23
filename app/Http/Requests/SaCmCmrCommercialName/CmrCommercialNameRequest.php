<?php
namespace App\Http\Requests\SaCmCmrCommercialName;

use Illuminate\Foundation\Http\FormRequest;

class CmrCommercialNameRequest extends FormRequest
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
            'commercialName'          => 'nullable|string',
            'substanceName'           => 'nullable|string',
            'molecularFormula'        => 'nullable|string',
            'chemicalFormType'        => 'nullable|string',
            'reachRegistrationNumber' => 'nullable|string',
            'whereAndWhyUse'          => 'nullable|string',
            'zdhcCategory'            => 'nullable|string',
            'zdhcLevel'               => 'nullable|string',
            'casNumber'               => 'nullable|string',
            'colourIndex'             => 'nullable|string',
            'useOfPPE'                => 'nullable|array',
            'hazardType'              => 'nullable|array',
            'ghsClassification'       => 'nullable|string',
        ];
    }
}
