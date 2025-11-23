<?php
namespace App\Http\Requests\SaCmChemicalManagementRecode;

use Illuminate\Foundation\Http\FormRequest;

class ChemicalManagementRecodeRequest extends FormRequest
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
            'commercialName'          => 'required|string',
            'substanceName'           => 'nullable|string',
            'reachRegistrationNumber' => 'nullable|string',
            'molecularFormula'        => 'nullable|string',
            'requestQuantity'         => 'required|numeric',
            'requestUnit'             => 'required|string',
            'zdhcCategory'            => 'required|string',
            'status'                  => 'nullable|string',
            'chemicalFormType'        => 'required|string',
            'whereAndWhyUse'          => 'required|string',
            'productStandard'         => 'required|string',
            'doYouHaveMSDSorSDS'      => 'nullable|string',
            'msdsorsdsIssuedDate'     => 'nullable|string',
            'msdsorsdsExpiryDate'     => 'nullable|string',
            'division'                => 'required|string',
            'requestedCustomer'       => 'nullable|string',
            'requestedMerchandiser'   => 'nullable|string',
            'requestDate'             => 'required|string',
            'reviewerId'              => 'required|numeric',
            'approverId'              => 'nullable|numeric',
            'hazardType'              => 'nullable|array',
            'useOfPPE'                => 'nullable|array',
            'ghsClassification'       => 'nullable|string',
            'zdhcLevel'               => 'nullable|string',
            'casNumber'               => 'nullable|string',
            'colourIndex'             => 'nullable|string',
            'removeDoc'               => 'nullable|array',
            'documents'               => 'nullable|array',
            'documents.*'             => 'file|mimes:pdf,doc,docx,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,webp',
            'createdByUser'           => 'nullable|string',
            'updatedBy'               => 'nullable|string',
        ];
    }
}
