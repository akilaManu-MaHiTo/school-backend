<?php
namespace App\Http\Requests\SaCmPurchaseInventoryRecord;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseInventoryRecordRequest extends FormRequest
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
            'commercialName'                             => 'required|string',
            'substanceName'                              => 'nullable|string',
            'reachRegistrationNumber'                    => 'nullable|string',
            'molecularFormula'                           => 'nullable|string',
            'requestQuantity'                            => 'required|numeric',
            'requestUnit'                                => 'required|string',
            'zdhcCategory'                               => 'required|string',
            'chemicalFormType'                           => 'required|string',
            'whereAndWhyUse'                             => 'required|string',
            'productStandard'                            => 'required|string',
            'doYouHaveMSDSorSDS'                         => 'nullable|string',
            'msdsorsdsIssuedDate'                        => 'nullable|string',
            'msdsorsdsExpiryDate'                        => 'nullable|string',
            'division'                                   => 'required|string',
            'requestedCustomer'                          => 'nullable|string',
            'requestedMerchandiser'                      => 'nullable|string',
            'requestDate'                                => 'required|string',
            'reviewerId'                                 => 'required|string',
            'approverId'                                 => 'nullable|string',
            'publishedBy'                                => 'nullable|string',
            'hazardType'                                 => 'nullable|array',
            'useOfPPE'                                   => 'nullable|array',
            'ghsClassification'                          => 'nullable|string',
            'zdhcLevel'                                  => 'nullable|string',
            'status'                                     => 'nullable|string',
            'casNumber'                                  => 'nullable|string',
            'colourIndex'                                => 'nullable|string',
            'removeDoc'                                  => 'nullable|array',
            'documents'                                  => 'nullable|array',
            'documents.*'                                => 'file|mimes:pdf,doc,docx,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,webp',
            'type'                                       => 'required|string',
            'name'                                       => 'required|string',
            'manufactureName'                            => 'nullable|string',
            'contactNumber'                              => 'nullable|string',
            'emailId'                                    => 'nullable|string',
            'location'                                   => 'nullable|string',
            'compliantWithTheLatestVersionOfZDHCandMRSL' => 'required|string',
            'apeoOrNpeFreeComplianceStatement'           => 'nullable|string',
            'manufacturingDate'                          => 'nullable|string',
            'expiryDate'                                 => 'nullable|string',
            'deliveryDate'                               => 'nullable|string',
            'deliveryQuantity'                           => 'required|numeric',
            'deliveryUnit'                               => 'required|string',
            'purchaseAmount'                             => 'required|string',
            'thresholdLimit'                             => 'required|string',
            'invoiceDate'                                => 'nullable|string',
            'invoiceReference'                           => 'nullable|string',
            'hazardStatement'                            => 'nullable|string',
            'storageConditionRequirements'               => 'nullable|string',
            'storagePlace'                               => 'nullable|string',
            'lotNumber'                                  => 'nullable|string',
            'removeCertificate'                          => 'nullable|array',
            'removeCertificate.*'                        => 'integer',
            'certificate'                                => 'nullable|array',
            'certificate.*.inventoryId'                  => 'nullable|numeric',
            'certificate.*.testName'                     => 'required|string',
            'certificate.*.testDate'                     => 'required|string',
            'certificate.*.testLab'                      => 'required|string',
            'certificate.*.issuedDate'                   => 'required|string',
            'certificate.*.expiryDate'                   => 'required|string',
            'certificate.*.positiveList'                 => 'required|string',
            'certificate.*.description'                  => 'required|string',
            'certificate.*.documents'                    => 'nullable|array',
            'certificate.*.documents.*'                  => 'nullable|file|mimes:pdf,doc,docx,xlsx,ppt,pptx,jpg,jpeg,png,gif,zip,webp',
            'certificate.*.removeDoc'                    => 'nullable|string',

        ];
    }
}
