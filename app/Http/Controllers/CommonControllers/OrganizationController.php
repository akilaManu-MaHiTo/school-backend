<?php
namespace App\Http\Controllers\CommonControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComOrganization\OrganizationRequest;
use App\Repositories\All\ComOrganization\ComOrganizationInterface;
use App\Services\OrganizationService;

class OrganizationController extends Controller
{
    protected $comOrganizationInterface;
    protected $organizationService;

    public function __construct(ComOrganizationInterface $comOrganizationInterface, OrganizationService $organizationService)
    {
        $this->comOrganizationInterface = $comOrganizationInterface;
        $this->organizationService      = $organizationService;
    }

public function index()
{
    $organization = $this->comOrganizationInterface->all()->first();

    if ($organization) {
        if (!empty($organization->logoUrl)) {
            $imageData = $this->organizationService->getImageUrl($organization->logoUrl);
            $organization->logoUrl = [
                'signedUrl'  => $imageData['signedUrl'],
                'fileName'   => $imageData['fileName'],
                'gsutil_uri' => $organization->logoUrl,
            ];
        }

        if (!empty($organization->insightImage)) {
            $imageData = $this->organizationService->getImageUrl($organization->insightImage);
            $organization->insightImage = [
                'signedUrl'  => $imageData['signedUrl'],
                'fileName'   => $imageData['fileName'],
                'gsutil_uri' => $organization->insightImage,
            ];
        }
    }

    return response()->json($organization);
}


    public function update($id, OrganizationRequest $request, OrganizationService $orgService)
    {
        $data     = $request->validated();
        $existing = $this->comOrganizationInterface->findById($id);

        if (! $existing) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        if ($request->has('removeLogo') && $existing->logoUrl) {
            $orgService->removeOldDocumentFromStorage($existing->logoUrl);
            $data['logoUrl'] = null;
        }

        if ($request->hasFile('logoUrl')) {
            $logo            = $orgService->uploadImageToGCS($request->file('logoUrl'));
            $data['logoUrl'] = $logo['gsutil_uri'];
        }

        if ($request->has('removeInsightImage') && $existing->insightImage) {
            $orgService->removeOldDocumentFromStorage($existing->insightImage);
            $data['insightImage'] = null;
        }

        if ($request->hasFile('insightImage')) {
            $insight              = $orgService->uploadImageToGCS($request->file('insightImage'));
            $data['insightImage'] = $insight['gsutil_uri'];
        }

        if (isset($data['colorPallet'])) {
            $data['colorPallet'] = json_encode($data['colorPallet']);
        }

        $updated = $this->comOrganizationInterface->update($id, $data);

        return response()->json([
            'message' => 'Organization updated successfully.',
            'data'    => $updated,
        ]);
    }

    public function destroy($id, OrganizationService $orgService)
    {
        $existing = $this->comOrganizationInterface->findById($id);

        if (! $existing) {
            return response()->json(['message' => 'Organization not found'], 404);
        }

        if ($existing->logoUrl) {
            $orgService->removeOldDocumentFromStorage($existing->logoUrl);
        }

        if ($existing->insightImage) {
            $orgService->removeOldDocumentFromStorage($existing->insightImage);
        }

        return $this->comOrganizationInterface->deleteById($id);
    }

}
