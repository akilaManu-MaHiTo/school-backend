<?php
namespace App\Http\Controllers\SustainabilityAppsControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaCmChemicalManagementRecode\ChemicalManagementRecodeRequest;
use App\Repositories\All\SaCmChemicalManagementRecode\ChemicalManagementRecodeInterface;
use App\Repositories\All\SaCmPurchaseInventory\PurchaseInventoryInterface;
use App\Repositories\All\User\UserInterface;
use App\Services\ChemicalManagementService;
use Illuminate\Support\Facades\Auth;

class SaCmChemicalManagementRecodeController extends Controller
{

    protected $chemicalManagementRecodeInterface;
    protected $userInterface;
    protected $chemicalManagementService;
    protected $purchaseInventoryInterface;

    public function __construct(ChemicalManagementRecodeInterface $chemicalManagementRecodeInterface, UserInterface $userInterface, ChemicalManagementService $chemicalManagementService, PurchaseInventoryInterface $purchaseInventoryInterface)
    {
        $this->chemicalManagementRecodeInterface = $chemicalManagementRecodeInterface;
        $this->userInterface                     = $userInterface;
        $this->chemicalManagementService         = $chemicalManagementService;
        $this->purchaseInventoryInterface        = $purchaseInventoryInterface;
    }

    public function index()
    {
        $chemical = $this->chemicalManagementRecodeInterface->All()->sortByDesc('created_at')->sortByDesc('updated_at')->values();

        $chemical = $chemical->map(function ($chemical) {
            try {
                $reviewer           = $this->userInterface->getById($chemical->reviewerId);
                $chemical->reviewer = $reviewer ? ['name' => $reviewer->name, 'id' => $reviewer->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $chemical->reviewer = ['name' => 'Unknown', 'id' => null];
            }

            try {
                $creator                     = $this->userInterface->getById($chemical->createdByUser);
                $chemical->createdByUserName = $creator ? $creator->name : 'Unknown';
            } catch (\Exception $e) {
                $chemical->createdByUserName = ['name' => 'Unknown', 'id' => null];
            }
            try {
                $creator                     = $this->userInterface->getById($chemical->approverId);
                $chemical->approverName = $creator ? $creator->name : 'Unknown';
            } catch (\Exception $e) {
                $chemical->approverName = ['name' => 'Unknown', 'id' => null];
            }
             try {
                $creator                     = $this->userInterface->getById($chemical->approvedBy);
                $chemical->approvedBy = $creator ? $creator->name : 'Unknown';
            } catch (\Exception $e) {
                $chemical->approvedBy = ['name' => 'Unknown', 'id' => null];
            }

            if (! empty($chemical->documents) && is_string($chemical->documents)) {
                $decodedDocuments = json_decode($chemical->documents, true);
                $documents        = is_array($decodedDocuments) ? $decodedDocuments : [];
            } else {
                $documents = is_array($chemical->documents) ? $chemical->documents : [];
            }

            foreach ($documents as &$item) {
                if (isset($item['gsutil_uri'])) {
                    $imageData        = $this->chemicalManagementService->getImageUrl($item['gsutil_uri']);
                    $item['fileName'] = $imageData['fileName'];
                    $item['imageUrl'] = $imageData['signedUrl'];
                }
            }

            $chemical->documents = $documents;
            return $chemical;
        });

        return response()->json($chemical, 200);
    }

    public function store(ChemicalManagementRecodeRequest $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userId                         = $user->id;
        $validatedData                  = $request->validated();
        $validatedData['createdByUser'] = $userId;

        $chemical = $this->chemicalManagementRecodeInterface->create($validatedData);

        if (! $chemical) {
            return response()->json(['message' => 'Failed to create Chemical Management Recode'], 500);
        }
        $uploadedFiles = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $uploadedFiles[] = $this->chemicalManagementService->uploadImageToGCS($file);
            }
        }

        if (! empty($uploadedFiles)) {
            $decodedDocuments = (! empty($record->documents) && is_string($chemical->documents))
            ? json_decode($chemical->documents, true)
            : [];

            if (! is_array($decodedDocuments)) {
                $decodedDocuments = [];
            }

            $mergedDocuments = array_merge($decodedDocuments, $uploadedFiles);

            $this->chemicalManagementRecodeInterface->update($chemical->id, [
                'documents' => json_encode($mergedDocuments),
            ]);
        }
        return response()->json([
            'message'       => 'Chemical Management Recode created successfully!',
            'externalAudit' => $chemical,
        ], 201);
    }

    public function update($id, ChemicalManagementRecodeRequest $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userId                     = $user->id;
        $validatedData              = $request->validated();
        $validatedData['updatedBy'] = $userId;

        $chemical      = $this->chemicalManagementRecodeInterface->findById($id);
        $validatedData = $request->validated();

        $documents = json_decode($chemical->documents, true) ?? [];

        if ($request->has('removeDoc')) {
            $removeDocs = $request->input('removeDoc');

            if (is_array($removeDocs)) {
                foreach ($removeDocs as $removeDoc) {
                    $this->chemicalManagementService->removeOldDocumentFromStorage($removeDoc);
                }

                $documents = array_values(array_filter($documents, function ($evidenceItem) use ($removeDocs) {
                    return ! in_array($evidenceItem['gsutil_uri'], $removeDocs);
                }));
            }
        }

        if ($request->hasFile('documents')) {
            $newEvidence = [];
            $newFiles    = $request->file('documents');

            foreach ($newFiles as $newFile) {
                $uploadResult = $this->chemicalManagementService->updateDocuments($newFile);
                if ($uploadResult && isset($uploadResult['gsutil_uri'])) {
                    $newEvidence[] = [
                        'gsutil_uri' => $uploadResult['gsutil_uri'],
                        'file_name'  => $uploadResult['file_name'],
                    ];
                }
            }

            $documents = array_merge($documents, $newEvidence);
        }

        $data['documents'] = json_encode($documents);

        $updated = $chemical->update($validatedData);

        if ($updated) {
            return response()->json([
                'message'  => 'Chemical Management Recode updated successfully!',
                'chemical' => $this->chemicalManagementRecodeInterface->findById($id),
            ], 200);
        } else {
            return response()->json(['message' => 'Failed to update the Chemical Management Recode.'], 500);
        }
    }

    public function approvedStatus($id)
    {

        $chemicalRecord = $this->chemicalManagementRecodeInterface->findById($id);

        if (! $chemicalRecord) {
            return response()->json(['message' => 'Chemical record not found.'], 404);
        }
         $user = Auth::user();
        $approverId = $user->id;

        $this->chemicalManagementRecodeInterface->update($id, [
            'status'     => 'approved',
            'approverId' => $approverId,
        ]);

        $this->chemicalManagementRecodeInterface->update($id, ['status' => 'approved']);

        $inventoryData = [
            'referenceNumber'         => $chemicalRecord->referenceNumber,
            'inventoryNumber'         => $chemicalRecord->id,
            'commercialName'          => $chemicalRecord->commercialName,
            'substanceName'           => $chemicalRecord->substanceName,
            'reachRegistrationNumber' => $chemicalRecord->reachRegistrationNumber,
            'molecularFormula'        => $chemicalRecord->molecularFormula,
            'requestQuantity'         => $chemicalRecord->requestQuantity,
            'requestUnit'             => $chemicalRecord->requestUnit,
            'zdhcCategory'            => $chemicalRecord->zdhcCategory,
            'status'                  => 'approved',
            'chemicalFormType'        => $chemicalRecord->chemicalFormType,
            'whereAndWhyUse'          => $chemicalRecord->whereAndWhyUse,
            'productStandard'         => $chemicalRecord->productStandard,
            'doYouHaveMSDSorSDS'      => $chemicalRecord->doYouHaveMSDSorSDS,
            'msdsorsdsIssuedDate'     => $chemicalRecord->msdsorsdsIssuedDate,
            'msdsorsdsExpiryDate'     => $chemicalRecord->msdsorsdsExpiryDate,
            'division'                => $chemicalRecord->division,
            'requestedCustomer'       => $chemicalRecord->requestedCustomer,
            'requestedMerchandiser'   => $chemicalRecord->requestedMerchandiser,
            'requestDate'             => $chemicalRecord->requestDate,
            'reviewerId'              => $chemicalRecord->reviewerId,
            'approverId'              => $chemicalRecord->approverId,
            'hazardType'              => $chemicalRecord->hazardType,
            'useOfPPE'                => $chemicalRecord->useOfPPE,
            'ghsClassification'       => $chemicalRecord->ghsClassification,
            'zdhcLevel'               => $chemicalRecord->zdhcLevel,
            'casNumber'               => $chemicalRecord->casNumber,
            'colourIndex'             => $chemicalRecord->colourIndex,
            'documents'               => $chemicalRecord->documents,
            'createdByUser'           => $chemicalRecord->createdByUser,
            'approverId'              => $approverId,
        ];

        $this->purchaseInventoryInterface->create($inventoryData);

        return response()->json([
            'message' => 'Chemical record approved and added to inventory.',
        ], 200);
    }

    public function destroy($id)
    {
        $chemical = $this->chemicalManagementRecodeInterface->findById((int) $id);

        $deleted = $this->chemicalManagementRecodeInterface->deleteById($id);
        if ($chemical->documents) {
            $documents = json_decode($chemical->documents, true);

            if (is_array($documents)) {
                foreach ($documents as $item) {
                    if (isset($item['gsutil_uri'])) {
                        $this->chemicalManagementService->deleteImageFromGCS($item['gsutil_uri']);
                    }
                }
            }
        }

        return response()->json([
            'message' => $deleted ? 'Record deleted successfully!' : 'Failed to delete record.',
        ], $deleted ? 200 : 500);
    }


    public function assignee()
    {
        $user        = Auth::user();
        $targetLevel = $user->assigneeLevel + 1;

        $assignees = $this->userInterface->getUsersByAssigneeLevelAndSection($targetLevel, 'Chemical Management Section')
            ->where('availability', 1)
            ->values();

        return response()->json($assignees);
    }

}
