<?php
namespace App\Http\Requests\ComOrganization;

use Illuminate\Foundation\Http\FormRequest;

class OrganizationRequest extends FormRequest
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
            'organizationName'        => 'nullable|string',
            'organizationFactoryName' => 'nullable|string',
            'logoUrl'                 => 'nullable|file|mimes:pdf,doc,docx,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,webp,svg',
            'removeLogo'              => 'nullable|string',
            'colorPallet'             => 'nullable|array',
            'insightImage'            => 'nullable|file|mimes:pdf,doc,docx,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,webp,svg',
            'removeInsightImage'      => 'nullable|string',
            'insightDescription'      => 'nullable|string',
            'status'                  => 'nullable|string',
            'createdByUser'           => 'nullable|string',
            'updatedBy'               => 'nullable|string',
        ];
    }

}
