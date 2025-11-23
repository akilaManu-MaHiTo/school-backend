<?php
namespace App\Http\Controllers\SustainabilityAppsControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaCmPurchaseInventoryRecord\PurchaseInventoryRecordRequest;
use App\Repositories\All\SaCmChemicalManagementRecode\ChemicalManagementRecodeInterface;
use App\Repositories\All\SaCmPirCertificateRecord\CertificateRecordInterface;
use App\Repositories\All\SaCmPurchaseInventory\PurchaseInventoryInterface;
use App\Repositories\All\User\UserInterface;
use App\Services\ChemicalManagementService;
use App\Services\PirCertificationRecodeService;
use Illuminate\Support\Facades\Auth;

class SaCmPurchaseInventoryRecodeController extends Controller
{

    protected $purchaseInventoryInterface;
    protected $userInterface;
    protected $chemicalManagementService;
    protected $certificationRecodeService;
    protected $certificateRecordInterface;
    protected $chemicalManagementRecodeInterface;

    public function __construct(PurchaseInventoryInterface $purchaseInventoryInterface, ChemicalManagementRecodeInterface $chemicalManagementRecodeInterface, ChemicalManagementService $chemicalManagementService, PirCertificationRecodeService $certificationRecodeService, UserInterface $userInterface, CertificateRecordInterface $certificateRecordInterface)
    {
        $this->purchaseInventoryInterface        = $purchaseInventoryInterface;
        $this->userInterface                     = $userInterface;
        $this->chemicalManagementService         = $chemicalManagementService;
        $this->certificationRecodeService        = $certificationRecodeService;
        $this->certificateRecordInterface        = $certificateRecordInterface;
        $this->chemicalManagementRecodeInterface = $chemicalManagementRecodeInterface;

    }
    public function index()
    {
        $records = $this->purchaseInventoryInterface->All()->sortByDesc('created_at')->sortByDesc('updated_at')->values();

        foreach ($records as &$record) {
            $documents = [];
            if (! empty($record->documents) && is_string($record->documents)) {
                $documents = json_decode($record->documents, true);
            } elseif (is_array($record->documents)) {
                $documents = $record->documents;
            }

            foreach ($documents as &$doc) {
                if (isset($doc['gsutil_uri'])) {
                    $urlData         = $this->chemicalManagementService->getImageUrl($doc['gsutil_uri']);
                    $doc['imageUrl'] = $urlData['signedUrl'];
                    $doc['fileName'] = $urlData['fileName'];
                }
            }
            $record->documents = $documents;

            $certificates = $this->certificateRecordInterface->findByInventoryId($record->id);

            $certificates = is_array($certificates) ? $certificates : collect($certificates)->all();

            foreach ($certificates as &$certificate) {
                $certificateDocs = [];

                if (! empty($certificate->documents) && is_string($certificate->documents)) {
                    $certificateDocs = json_decode($certificate->documents, true);
                } elseif (is_array($certificate->documents)) {
                    $certificateDocs = $certificate->documents;
                }

                foreach ($certificateDocs as &$doc) {
                    if (isset($doc['gsutil_uri'])) {
                        $urlData         = $this->certificationRecodeService->getImageUrl($doc['gsutil_uri']);
                        $doc['imageUrl'] = $urlData['signedUrl'];
                        $doc['fileName'] = $urlData['fileName'];
                    }
                }

                $certificate->documents = $certificateDocs;
            }

            $record->certificate = $certificates;

            try {
                $creator               = $this->userInterface->getById($record->createdByUser);
                $record->createdByUser = $creator ? ['name' => $creator->name, 'id' => $creator->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $record->createdByUser = ['name' => 'Unknown', 'id' => null];
            }

            try {
                $updater               = $this->userInterface->getById($record->updatedBy);
                $record->updatedByUser = $updater ? ['name' => $updater->name, 'id' => $updater->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $record->updatedByUser = ['name' => 'Unknown', 'id' => null];
            }

            try {
                $reviewer         = $this->userInterface->getById($record->reviewerId);
                $record->reviewer = $reviewer ? ['name' => $reviewer->name, 'id' => $reviewer->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $record->reviewer = ['name' => 'Unknown', 'id' => null];
            }

            try {
                $creator              = $this->userInterface->getById($record->approverId);
                $record->approverName = $creator ? ['name' => $creator->name, 'id' => $creator->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $record->approverName = ['name' => 'Unknown', 'id' => null];
            }
            try {
                $creator            = $this->userInterface->getById($record->approvedBy);
                $record->approvedBy = $creator ? ['name' => $creator->name, 'id' => $creator->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $record->approvedByUser = ['name' => 'Unknown', 'id' => null];
            }

            try {
                $publisher               = $this->userInterface->getById($record->publishedBy);
                $record->publishedByUser = $publisher ? ['name' => $publisher->name, 'id' => $publisher->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $record->publishedByUser = ['name' => 'Unknown', 'id' => null];
            }

        }
        return response()->json($records, 200);
    }

    public function publishStatus($id, PurchaseInventoryRecordRequest $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userId                       = $user->id;
        $validatedData                = $request->validated();
        $validatedData['publishedBy'] = $userId;
        $validatedData['status']      = 'published';

        $targetSetting = $this->purchaseInventoryInterface->findById($id);

        $documents = json_decode($targetSetting->documents, true) ?? [];

        if ($request->has('removeDoc')) {
            $removeDocs = $request->input('removeDoc');
            if (is_array($removeDocs)) {
                foreach ($removeDocs as $removeDoc) {
                    $this->chemicalManagementService->removeOldDocumentFromStorage($removeDoc);
                }

                $documents = array_values(array_filter($documents, function ($doc) use ($removeDocs) {
                    return ! in_array($doc['gsutil_uri'], $removeDocs);
                }));
            }
        }

        if ($request->hasFile('documents')) {
            $newDocuments = [];
            $existingUris = array_column($documents, 'gsutil_uri');

            foreach ($request->file('documents') as $newFile) {
                $uploadResult = $this->chemicalManagementService->updateDocuments($newFile);

                if ($uploadResult && isset($uploadResult['gsutil_uri'])) {
                    $gsutilUri = $uploadResult['gsutil_uri'];

                    if (! in_array($gsutilUri, $existingUris)) {
                        $newDocuments[] = [
                            'gsutil_uri' => $gsutilUri,
                            'file_name'  => $uploadResult['file_name'] ?? basename($gsutilUri),
                        ];
                        $existingUris[] = $gsutilUri;
                    }
                }
            }

            $documents = array_merge($documents, $newDocuments);
        }

        $validatedData['documents'] = json_encode($documents);

        $updatedRecord = $this->purchaseInventoryInterface->update($id, $validatedData);

        if (! $updatedRecord) {
            return response()->json([
                'message' => 'Failed to publish purchase inventory record.',
            ], 500);
        }

        $updatedRecord = $this->purchaseInventoryInterface->findById($id);
        if ($request->has('removeCertificate')) {
            $removeIds = $request->input('removeCertificate');
            if (is_array($removeIds)) {
                foreach ($removeIds as $certId) {
                    $certificate = $this->certificateRecordInterface->findById($certId);
                    if ($certificate) {
                        if (! empty($certificate->documents)) {
                            $docs = json_decode($certificate->documents, true);
                            foreach ($docs as $doc) {
                                if (isset($doc['gsutil_uri'])) {
                                    $this->certificationRecodeService->removeOldDocumentFromStorage($doc['gsutil_uri']);
                                }
                            }
                        }

                        $this->certificateRecordInterface->deleteById($certId);
                    }
                }
            }
        }

        if (! empty($validatedData['certificate'])) {
            foreach ($validatedData['certificate'] as $index => $certificateData) {
                $certDocs = [];

                if ($request->hasFile("certificate.{$index}.documents")) {
                    foreach ($request->file("certificate.{$index}.documents") as $certFile) {
                        $uploadResult = $this->certificationRecodeService->uploadImageToGCS($certFile);
                        if ($uploadResult && isset($uploadResult['gsutil_uri'])) {
                            $certDocs[] = [
                                'gsutil_uri' => $uploadResult['gsutil_uri'],
                                'file_name'  => $uploadResult['file_name'] ?? basename($uploadResult['gsutil_uri']),
                            ];
                        }
                    }
                }

                $certificateData['inventoryId'] = $updatedRecord->id;
                $certificateData['documents']   = json_encode($certDocs);

                $this->certificateRecordInterface->create($certificateData);
            }
        }

        return response()->json([
            'message' => 'Purchase inventory record published successfully.',
            'data'    => $updatedRecord,
        ]);
    }

    public function update($id, PurchaseInventoryRecordRequest $request)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userId                     = $user->id;
        $validatedData              = $request->validated();
        $validatedData['updatedBy'] = $userId;
        $targetSetting              = $this->purchaseInventoryInterface->findById($id);

        $documents = json_decode($targetSetting->documents, true) ?? [];

        if ($request->has('removeDoc')) {
            $removeDocs = $request->input('removeDoc');
            if (is_array($removeDocs)) {
                foreach ($removeDocs as $removeDoc) {
                    $this->chemicalManagementService->removeOldDocumentFromStorage($removeDoc);
                }

                $documents = array_values(array_filter($documents, function ($doc) use ($removeDocs) {
                    return ! in_array($doc['gsutil_uri'], $removeDocs);
                }));
            }
        }

        if ($request->hasFile('documents')) {
            $newDocuments = [];
            $existingUris = array_column($documents, 'gsutil_uri');

            foreach ($request->file('documents') as $newFile) {
                $uploadResult = $this->chemicalManagementService->updateDocuments($newFile);

                if ($uploadResult && isset($uploadResult['gsutil_uri'])) {
                    $gsutilUri = $uploadResult['gsutil_uri'];

                    if (! in_array($gsutilUri, $existingUris)) {
                        $newDocuments[] = [
                            'gsutil_uri' => $gsutilUri,
                            'file_name'  => $uploadResult['file_name'] ?? basename($gsutilUri),
                        ];
                        $existingUris[] = $gsutilUri;
                    }
                }
            }

            $documents = array_merge($documents, $newDocuments);
        }

        $validatedData['documents'] = json_encode($documents);

        $updatedRecord = $this->purchaseInventoryInterface->update($id, $validatedData);

        if (! $updatedRecord) {
            return response()->json([
                'message' => 'Failed to draft purchase inventory record.',
            ], 500);
        }

        $updatedRecord = $this->purchaseInventoryInterface->findById($id);
        if ($request->has('removeCertificate')) {
            $removeIds = $request->input('removeCertificate');
            if (is_array($removeIds)) {
                foreach ($removeIds as $certId) {
                    $certificate = $this->certificateRecordInterface->findById($certId);
                    if ($certificate) {
                        if (! empty($certificate->documents)) {
                            $docs = json_decode($certificate->documents, true);
                            foreach ($docs as $doc) {
                                if (isset($doc['gsutil_uri'])) {
                                    $this->certificationRecodeService->removeOldDocumentFromStorage($doc['gsutil_uri']);
                                }
                            }
                        }

                        $this->certificateRecordInterface->deleteById($certId);
                    }
                }
            }
        }

        if (! empty($validatedData['certificate'])) {
            foreach ($validatedData['certificate'] as $index => $certificateData) {
                $certDocs = [];

                if ($request->hasFile("certificate.{$index}.documents")) {
                    foreach ($request->file("certificate.{$index}.documents") as $certFile) {
                        $uploadResult = $this->certificationRecodeService->uploadImageToGCS($certFile);
                        if ($uploadResult && isset($uploadResult['gsutil_uri'])) {
                            $certDocs[] = [
                                'gsutil_uri' => $uploadResult['gsutil_uri'],
                                'file_name'  => $uploadResult['file_name'] ?? basename($uploadResult['gsutil_uri']),
                            ];
                        }
                    }
                }

                $certificateData['inventoryId'] = $updatedRecord->id;
                $certificateData['documents']   = json_encode($certDocs);

                $this->certificateRecordInterface->create($certificateData);
            }
        }

        return response()->json([
            'message' => 'Purchase inventory record draft successfully.',
            'data'    => $updatedRecord,
        ]);
    }

    public function destroy($id)
    {
        $record = $this->purchaseInventoryInterface->getById($id);
        if (! $record) {
            return response()->json([
                'message' => 'Purchase inventory record not found.',
            ], 404);
        }

        $documents = json_decode($record->documents, true) ?? [];
        foreach ($documents as $doc) {
            if (! empty($doc['gsutil_uri'])) {
                $this->chemicalManagementService->removeOldDocumentFromStorage($doc['gsutil_uri']);
            }
        }

        $certificates = $this->certificateRecordInterface->findByInventoryId($id);
        foreach ($certificates as $certificate) {
            $certDocs = json_decode($certificate->documents, true) ?? [];
            foreach ($certDocs as $doc) {
                if (! empty($doc['gsutil_uri'])) {
                    $this->certificationRecodeService->removeOldDocumentFromStorage($doc['gsutil_uri']);
                }
            }

            $this->certificateRecordInterface->deleteByInventoryId($id);
        }

        $deleted = $this->purchaseInventoryInterface->deleteById($id);

        if (! $deleted) {

            return response()->json([
                'message' => 'Failed to delete purchase inventory record.',
            ], 500);
        }

        return response()->json([
            'message' => 'Purchase inventory record deleted successfully.',
        ], 200);
    }

    public function assignTask()
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $records = $this->purchaseInventoryInterface
            ->getByReviewerId($user->id)
            ->filter(function ($risk) {
                return $risk->status !== 'Approved';
            })
            ->sortByDesc('created_at')
            ->sortByDesc('updated_at')
            ->values();

        foreach ($records as &$record) {
            $documents = [];
            if (! empty($record->documents) && is_string($record->documents)) {
                $documents = json_decode($record->documents, true);
            } elseif (is_array($record->documents)) {
                $documents = $record->documents;
            }

            foreach ($documents as &$doc) {
                if (isset($doc['gsutil_uri'])) {
                    $urlData         = $this->chemicalManagementService->getImageUrl($doc['gsutil_uri']);
                    $doc['imageUrl'] = $urlData['signedUrl'];
                    $doc['fileName'] = $urlData['fileName'];
                }
            }

            $record->documents = $documents;

            $certificate = $this->certificateRecordInterface->findByInventoryId($record->id);

            foreach ($certificate as &$certificate) {
                $certificateDocs = [];

                if (! empty($certificate->documents) && is_string($certificate->documents)) {
                    $certificateDocs = json_decode($certificate->documents, true);
                } elseif (is_array($certificate->documents)) {
                    $certificateDocs = $certificate->documents;
                }

                foreach ($certificateDocs as &$doc) {
                    if (isset($doc['gsutil_uri'])) {
                        $urlData         = $this->certificationRecodeService->getImageUrl($doc['gsutil_uri']);
                        $doc['imageUrl'] = $urlData['signedUrl'];
                        $doc['fileName'] = $urlData['fileName'];
                    }
                }

                $certificate->documents = $certificateDocs;
            }

            $record->certificate = [$certificate] ? $certificate : null;

            try {
                $creator               = $this->userInterface->getById($record->createdByUser);
                $record->createdByUser = $creator ? ['name' => $creator->name, 'id' => $creator->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $record->createdByUser = ['name' => 'Unknown', 'id' => null];
            }

            try {
                $updater               = $this->userInterface->getById($record->updatedBy);
                $record->updatedByUser = $updater ? ['name' => $updater->name, 'id' => $updater->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $record->updatedByUser = ['name' => 'Unknown', 'id' => null];
            }

            try {
                $reviewer         = $this->userInterface->getById($record->reviewerId);
                $record->reviewer = $reviewer ? ['name' => $reviewer->name, 'id' => $reviewer->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $record->reviewer = ['name' => 'Unknown', 'id' => null];
            }

            try {
                $approver               = $this->userInterface->getById($record->approverId);
                $record->approvedByUser = $approver ? ['name' => $approver->name, 'id' => $approver->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $record->approvedByUser = ['name' => 'Unknown', 'id' => null];
            }

            try {
                $publisher               = $this->userInterface->getById($record->publishedBy);
                $record->publishedByUser = $publisher ? ['name' => $publisher->name, 'id' => $publisher->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $record->publishedByUser = ['name' => 'Unknown', 'id' => null];
            }
        }

        return response()->json($records, 200);
    }

    public function assignTaskApproved()
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $records = $this->purchaseInventoryInterface
            ->all()
            ->filter(function ($med) {
                return $med->status === 'approved';
            })
            ->sortByDesc('created_at')
            ->sortByDesc('updated_at')
            ->values();

        foreach ($records as &$record) {
            $documents = [];
            if (! empty($record->documents) && is_string($record->documents)) {
                $documents = json_decode($record->documents, true);
            } elseif (is_array($record->documents)) {
                $documents = $record->documents;
            }

            foreach ($documents as &$doc) {
                if (isset($doc['gsutil_uri'])) {
                    $urlData         = $this->chemicalManagementService->getImageUrl($doc['gsutil_uri']);
                    $doc['imageUrl'] = $urlData['signedUrl'];
                    $doc['fileName'] = $urlData['fileName'];
                }
            }

            $record->documents = $documents;

            $certificate = $this->certificateRecordInterface->findByInventoryId($record->id);

            foreach ($certificate as &$certificate) {
                $certificateDocs = [];

                if (! empty($certificate->documents) && is_string($certificate->documents)) {
                    $certificateDocs = json_decode($certificate->documents, true);
                } elseif (is_array($certificate->documents)) {
                    $certificateDocs = $certificate->documents;
                }

                foreach ($certificateDocs as &$doc) {
                    if (isset($doc['gsutil_uri'])) {
                        $urlData         = $this->certificationRecodeService->getImageUrl($doc['gsutil_uri']);
                        $doc['imageUrl'] = $urlData['signedUrl'];
                        $doc['fileName'] = $urlData['fileName'];
                    }
                }

                $certificate->documents = $certificateDocs;
            }

            $record->certificate = [$certificate] ? $certificate : null;

            try {
                $creator               = $this->userInterface->getById($record->createdByUser);
                $record->createdByUser = $creator ? ['name' => $creator->name, 'id' => $creator->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $record->createdByUser = ['name' => 'Unknown', 'id' => null];
            }

            try {
                $updater               = $this->userInterface->getById($record->updatedBy);
                $record->updatedByUser = $updater ? ['name' => $updater->name, 'id' => $updater->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $record->updatedByUser = ['name' => 'Unknown', 'id' => null];
            }

            try {
                $reviewer         = $this->userInterface->getById($record->reviewerId);
                $record->reviewer = $reviewer ? ['name' => $reviewer->name, 'id' => $reviewer->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $record->reviewer = ['name' => 'Unknown', 'id' => null];
            }

            try {
                $approver               = $this->userInterface->getById($record->approverId);
                $record->approvedByUser = $approver ? ['name' => $approver->name, 'id' => $approver->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $record->approvedByUser = ['name' => 'Unknown', 'id' => null];
            }

            try {
                $publisher               = $this->userInterface->getById($record->publishedBy);
                $record->publishedByUser = $publisher ? ['name' => $publisher->name, 'id' => $publisher->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $record->publishedByUser = ['name' => 'Unknown', 'id' => null];
            }
        }

        return response()->json($records, 200);
    }

    public function updateStatusToApproved(string $id)
    {
        $user = Auth::user();

        if (! $user || $user->assigneeLevel != 5) {
            return response()->json([
                'message' => 'Unauthorized. Only CEO assignees can approve.',
            ], 403);
        }

        $record = $this->purchaseInventoryInterface->findById($id);

        if (! $record) {
            return response()->json([
                'message' => 'Purchase inventory record not found.',
            ], 404);
        }

        $updated = $this->purchaseInventoryInterface->update($id, [
            'status' => 'Approved',
        ]);

        if (! $updated) {
            return response()->json([
                'message' => 'Failed to approve the purchase inventory record.',
            ], 500);
        }

        $record = $this->purchaseInventoryInterface->findById($id);

        return response()->json([
            'message' => 'Purchase inventory record approved successfully!',
            'record'  => $record,
        ], 200);
    }

    public function getPublishedStatus()
    {
        $records = $this->purchaseInventoryInterface->All()
            ->where('status', 'published')
            ->sortByDesc('created_at')
            ->sortByDesc('updated_at')
            ->values();

        foreach ($records as &$record) {
            $documents = [];
            if (! empty($record->documents) && is_string($record->documents)) {
                $documents = json_decode($record->documents, true);
            } elseif (is_array($record->documents)) {
                $documents = $record->documents;
            }

            foreach ($documents as &$doc) {
                if (isset($doc['gsutil_uri'])) {
                    $urlData         = $this->chemicalManagementService->getImageUrl($doc['gsutil_uri']);
                    $doc['imageUrl'] = $urlData['signedUrl'];
                    $doc['fileName'] = $urlData['fileName'];
                }
            }
            $record->documents = $documents;

            $certificate = $this->certificateRecordInterface->findByInventoryId($record->id);
            foreach ($certificate as &$certificate) {
                $certificateDocs = [];
                if (! empty($certificate->documents) && is_string($certificate->documents)) {
                    $certificateDocs = json_decode($certificate->documents, true);
                } elseif (is_array($certificate->documents)) {
                    $certificateDocs = $certificate->documents;
                }

                foreach ($certificateDocs as &$doc) {
                    if (isset($doc['gsutil_uri'])) {
                        $urlData         = $this->certificationRecodeService->getImageUrl($doc['gsutil_uri']);
                        $doc['imageUrl'] = $urlData['signedUrl'];
                        $doc['fileName'] = $urlData['fileName'];
                    }
                }
                $certificate->documents = $certificateDocs;
            }

            $record->certificate = $certificate ? [$certificate] : [];
            try {
                $creator               = $this->userInterface->getById($record->createdByUser);
                $record->createdByUser = $creator ? ['name' => $creator->name, 'id' => $creator->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $record->createdByUser = ['name' => 'Unknown', 'id' => null];
            }

            try {
                $updater               = $this->userInterface->getById($record->updatedBy);
                $record->updatedByUser = $updater ? ['name' => $updater->name, 'id' => $updater->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $record->updatedByUser = ['name' => 'Unknown', 'id' => null];
            }

            try {
                $reviewer         = $this->userInterface->getById($record->reviewerId);
                $record->reviewer = $reviewer ? ['name' => $reviewer->name, 'id' => $reviewer->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $record->reviewer = ['name' => 'Unknown', 'id' => null];
            }

            try {
                $approver               = $this->userInterface->getById($record->approverId);
                $record->approvedByUser = $approver ? ['name' => $approver->name, 'id' => $approver->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $record->approvedByUser = ['name' => 'Unknown', 'id' => null];
            }

            try {
                $publisher               = $this->userInterface->getById($record->publishedBy);
                $record->publishedByUser = $publisher ? ['name' => $publisher->name, 'id' => $publisher->id] : ['name' => 'Unknown', 'id' => null];
            } catch (\Exception $e) {
                $record->publishedByUser = ['name' => 'Unknown', 'id' => null];
            }
        }

        return response()->json($records, 200);
    }

    public function getStockAmount($startDate, $endDate, $division)
    {
        $records = $this->purchaseInventoryInterface->filterByParams($startDate, $endDate, $division);

        $inStockFormulas = [];
        $deliveredTotal  = 0;
        $amountTotal     = 0;

        foreach ($records as $record) {
            if ($record->status !== 'published') {
                continue;
            }

            if (! empty($record->molecularFormula)) {
                $inStockFormulas[$record->molecularFormula] = true;
            }

            if (is_numeric($record->deliveryQuantity)) {
                $deliveredTotal += floatval($record->deliveryQuantity);
            }

            if (is_numeric($record->purchaseAmount)) {
                $amountTotal += floatval($record->purchaseAmount);
            }
        }

        return response()->json([
            'startDate'      => $startDate,
            'endDate'        => $endDate,
            'division'       => $division,
            'inStockCount'   => count($inStockFormulas),
            'deliveredTotal' => $deliveredTotal,
            'purchaseAmount' => round($amountTotal, 2),
        ]);
    }

    public function getMonthlyDelivery($startDate, $endDate, $division)
    {
        $records = $this->purchaseInventoryInterface->filterByParams($startDate, $endDate, $division);

        $aggregated = [];

        foreach ($records as $record) {
            if ($record->status !== 'published') {
                continue;
            }

            if (empty($record->deliveryDate) || empty($record->molecularFormula)) {
                continue;
            }

            $deliveryDate = \Carbon\Carbon::parse($record->deliveryDate);

            if ($deliveryDate->lt($startDate) || $deliveryDate->gt($endDate)) {
                continue;
            }

            $monthName = $deliveryDate->format('F');
            $formula   = $record->molecularFormula;
            $quantity  = is_numeric($record->deliveryQuantity) ? floatval($record->deliveryQuantity) : 0;

            if (! isset($aggregated[$monthName][$formula])) {
                $aggregated[$monthName][$formula] = 0;
            }

            $aggregated[$monthName][$formula] += $quantity;
        }

        $monthlyBreakdown = [];

        foreach ($aggregated as $month => $formulas) {
            foreach ($formulas as $formula => $quantity) {
                $monthlyBreakdown[] = [
                    'month'    => $month,
                    'chemical' => $formula,
                    'quantity' => $quantity,
                ];
            }
        }

        return response()->json([
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'division'  => $division,
            'data'      => $monthlyBreakdown,
        ]);
    }

    public function getLatestRecord($startDate, $endDate, $division)
    {
        $records = $this->purchaseInventoryInterface
            ->filterByParams($startDate, $endDate, $division)
            ->sortByDesc('updated_at');

        if ($records->isEmpty()) {
            return response()->json(['message' => 'No records found'], 404);
        }

        foreach ($records as $record) {
            $documents = [];
            if (! empty($record->documents) && is_string($record->documents)) {
                $documents = json_decode($record->documents, true);
            } elseif (is_array($record->documents)) {
                $documents = $record->documents;
            }

            foreach ($documents as &$doc) {
                if (isset($doc['gsutil_uri'])) {
                    $urlData         = $this->chemicalManagementService->getImageUrl($doc['gsutil_uri']);
                    $doc['imageUrl'] = $urlData['signedUrl'];
                    $doc['fileName'] = $urlData['fileName'];
                }
            }
            $record->documents = $documents;

            $certificates = $this->certificateRecordInterface->findByInventoryId($record->id);
            $certificates = is_array($certificates) ? $certificates : collect($certificates)->all();

            foreach ($certificates as &$certificate) {
                $certificateDocs = [];

                if (! empty($certificate->documents) && is_string($certificate->documents)) {
                    $certificateDocs = json_decode($certificate->documents, true);
                } elseif (is_array($certificate->documents)) {
                    $certificateDocs = $certificate->documents;
                }

                foreach ($certificateDocs as &$doc) {
                    if (isset($doc['gsutil_uri'])) {
                        $urlData         = $this->certificationRecodeService->getImageUrl($doc['gsutil_uri']);
                        $doc['imageUrl'] = $urlData['signedUrl'];
                        $doc['fileName'] = $urlData['fileName'];
                    }
                }

                $certificate->documents = $certificateDocs;
            }

            $record->certificate = $certificates;

            $record->createdByUser   = $this->getUserInfo($record->createdByUser);
            $record->updatedByUser   = $this->getUserInfo($record->updatedBy);
            $record->approvedByUser  = $this->getUserInfo($record->approverId);
            $record->publishedByUser = $this->getUserInfo($record->publishedBy);
        }

        return response()->json(array_values($records->toArray()));

    }

    public function getTransactionLatestRecord($startDate, $endDate, $division)
    {
        $records = $this->purchaseInventoryInterface
            ->filterByParams($startDate, $endDate, $division)
            ->where('status', 'published')
            ->sortByDesc('updated_at');

        if ($records->isEmpty()) {
            return response()->json(['message' => 'No published records found'], 404);
        }

        foreach ($records as $record) {
            $documents = is_string($record->documents) ? json_decode($record->documents, true) : ($record->documents ?? []);
            foreach ($documents as &$doc) {
                if (isset($doc['gsutil_uri'])) {
                    $urlData         = $this->chemicalManagementService->getImageUrl($doc['gsutil_uri']);
                    $doc['imageUrl'] = $urlData['signedUrl'];
                    $doc['fileName'] = $urlData['fileName'];
                }
            }
            $record->documents = $documents;

            $certificates = collect($this->certificateRecordInterface->findByInventoryId($record->id));
            foreach ($certificates as &$certificate) {
                $certificateDocs = is_string($certificate->documents) ? json_decode($certificate->documents, true) : ($certificate->documents ?? []);
                foreach ($certificateDocs as &$doc) {
                    if (isset($doc['gsutil_uri'])) {
                        $urlData         = $this->certificationRecodeService->getImageUrl($doc['gsutil_uri']);
                        $doc['imageUrl'] = $urlData['signedUrl'];
                        $doc['fileName'] = $urlData['fileName'];
                    }
                }
                $certificate->documents = $certificateDocs;
            }

            $record->certificate     = $certificates;
            $record->createdByUser   = $this->getUserInfo($record->createdByUser);
            $record->updatedByUser   = $this->getUserInfo($record->updatedBy);
            $record->approvedByUser  = $this->getUserInfo($record->approverId);
            $record->publishedByUser = $this->getUserInfo($record->publishedBy);

            $response[] = $record;
        }

        return response()->json($response);
    }

    private function getUserInfo($userId)
    {
        try {
            $user = $this->userInterface->getById($userId);
            return $user ? ['name' => $user->name, 'id' => $user->id] : ['name' => 'Unknown', 'id' => null];
        } catch (\Exception $e) {
            return ['name' => 'Unknown', 'id' => null];
        }
    }

    public function getStockThreshold($startDate, $endDate, $division)
    {
        $records = $this->purchaseInventoryInterface->filterByParams($startDate, $endDate, $division);

        $chemicals = [];

        foreach ($records as $record) {
            if ($record->status !== 'published') {
                continue;
            }

            if (empty($record->molecularFormula) || ! is_numeric($record->deliveryQuantity) || ! is_numeric($record->thresholdLimit)) {
                continue;
            }

            $formula        = $record->molecularFormula;
            $deliveryQty    = floatval($record->deliveryQuantity);
            $thresholdLimit = floatval($record->thresholdLimit);

            if (! isset($chemicals[$formula])) {
                $chemicals[$formula] = [
                    'chemicalName'  => $formula,
                    'totalLimit'    => 0,
                    'totalQuantity' => 0,
                ];
            }

            $chemicals[$formula]['totalLimit'] += $thresholdLimit;
            $chemicals[$formula]['totalQuantity'] += $deliveryQty;
        }

        foreach ($chemicals as &$data) {
            if ($data['totalLimit'] > 0) {
                $data['percentage'] = round(($data['totalQuantity'] / $data['totalLimit']) * 100, 2);
            } else {
                $data['percentage'] = 0;
            }
        }

        return response()->json([
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'division'  => $division,
            'data'      => array_values($chemicals),
        ]);
    }

    public function getHighestStock($startDate, $endDate, $division)
    {
        $records = $this->purchaseInventoryInterface->filterByParams($startDate, $endDate, $division);

        $chemicals = [];

        foreach ($records as $record) {
            if ($record->status !== 'published') {
                continue;
            }

            if (empty($record->molecularFormula) || ! is_numeric($record->deliveryQuantity)) {
                continue;
            }

            $formula     = $record->molecularFormula;
            $deliveryQty = floatval($record->deliveryQuantity);

            if (! isset($chemicals[$formula])) {
                $chemicals[$formula] = [
                    'chemicalName'  => $formula,
                    'totalQuantity' => 0,
                ];
            }

            $chemicals[$formula]['totalQuantity'] += $deliveryQty;
        }

        return response()->json([
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'division'  => $division,
            'data'      => array_values($chemicals),
        ]);
    }

    public function getStatusSummary($startDate, $endDate, $division)
    {
        $statusCounts = [];

        $purchaseRecords = $this->purchaseInventoryInterface->filterByParams($startDate, $endDate, $division);
        foreach ($purchaseRecords as $record) {
            $status = $record->status ?? 'unknown';
            if (! isset($statusCounts[$status])) {
                $statusCounts[$status] = 0;
            }
            $statusCounts[$status]++;
        }

        $chemicalRecords = $this->chemicalManagementRecodeInterface->filterByParams($startDate, $endDate, $division);
        foreach ($chemicalRecords as $record) {
            $status = $record->status ?? 'unknown';
            if (! isset($statusCounts[$status])) {
                $statusCounts[$status] = 0;
            }
            $statusCounts[$status]++;
        }

        $summary = [];
        foreach ($statusCounts as $status => $count) {
            $summary[] = [
                'status' => $status,
                'count'  => $count,
            ];
        }

        return response()->json([
            'startDate' => $startDate,
            'endDate'   => $endDate,
            'division'  => $division,
            'data'      => $summary,
        ]);
    }

    public function getChemicalInventoryInsights($startDate, $endDate, $division)
    {
        $records = $this->purchaseInventoryInterface->filterByParams($startDate, $endDate, $division)
            ->filter(fn($r) => $r->status === 'published');

        $totalRecords = $records->count();

        $supplierSummary = [];
        $totalQty        = 0;
        foreach ($records as $record) {
            $supplier = $record->name ?? 'Unknown';
            $qty      = floatval($record->deliveryQuantity ?? 0);
            $totalQty += $qty;

            if (! isset($supplierSummary[$supplier])) {
                $supplierSummary[$supplier] = [
                    'name'          => $supplier,
                    'recordCount'   => 0,
                    'totalQuantity' => 0,
                ];
            }
            $supplierSummary[$supplier]['recordCount']++;
            $supplierSummary[$supplier]['totalQuantity'] += $qty;
        }
        foreach ($supplierSummary as &$data) {
            $data['percentageOfTotalRecords'] = $totalRecords ? round(($data['recordCount'] / $totalRecords) * 100, 2) : 0;
        }
        $topSuppliers = array_values($supplierSummary);
        usort($topSuppliers, fn($a, $b) => $b['totalQuantity'] <=> $a['totalQuantity']);

        $positiveListSummary = [];
        $positiveListTotal   = 0;

        foreach ($records as $record) {
            $certificates = $this->certificateRecordInterface->findByInventoryId($record->id);
            foreach ($certificates as $cert) {
                $positive = $cert->positiveList ?? 'unknown';
                if (! isset($positiveListSummary[$positive])) {
                    $positiveListSummary[$positive] = 0;
                }
                $positiveListSummary[$positive]++;
                $positiveListTotal++;
            }
        }
        $positiveListBreakdown = [];
        foreach ($positiveListSummary as $positive => $count) {
            $positiveListBreakdown[] = [
                'positiveList'                  => $positive,
                'count'                         => $count,
                'percentageOfTotalCertificates' => $positiveListTotal ? round(($count / $positiveListTotal) * 100, 2) : 0,
            ];
        }

        $msdsExpiries = $records->filter(function ($r) use ($startDate, $endDate) {
            return ! empty($r->msdsorsdsExpiryDate) &&
            $r->msdsorsdsExpiryDate >= $startDate &&
            $r->msdsorsdsExpiryDate <= $endDate;
        })->values();

        $chemicalExpirySummary = $records->filter(function ($r) use ($startDate, $endDate) {
            return ! empty($r->expiryDate) &&
            $r->expiryDate >= $startDate &&
            $r->expiryDate <= $endDate;
        })->map(function ($r) {
            return [
                'molecularFormula' => $r->molecularFormula,
                'expiryDate'       => $r->expiryDate,
            ];
        })->values();

        $certificateExpiries = [];
        foreach ($records as $record) {
            $certificates = $this->certificateRecordInterface->findByInventoryId($record->id);
            foreach ($certificates as $cert) {
                if (! empty($cert->expiryDate) && $cert->expiryDate >= $startDate && $cert->expiryDate <= $endDate) {
                    $certificateExpiries[] = [
                        'inventoryId'  => $record->id,
                        'testName'     => $cert->testName,
                        'expiryDate'   => $cert->expiryDate,
                        'positiveList' => $cert->positiveList ?? null,
                    ];
                }
            }
        }

        return response()->json([
            'startDate'         => $startDate,
            'endDate'           => $endDate,
            'division'          => $division,
            'topSuppliers'      => $topSuppliers,
            'positiveList'      => $positiveListBreakdown,
            'msdsExpiries'      => $msdsExpiries,
            'chemicalExpiry'    => $chemicalExpirySummary,
            'certificateExpiry' => $certificateExpiries,
        ]);
    }

    public function getCategoryAndClassification($startDate, $endDate, $division)
    {
        $records = $this->purchaseInventoryInterface->filterByParams($startDate, $endDate, $division)
            ->filter(fn($r) => $r->status === 'published');

        $total = $records->count();

        $hazardTypes = [];
        foreach ($records as $record) {
            $types = $record->hazardType;

            if (is_string($types)) {
                $decoded = json_decode($types, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $types = $decoded;
                } else {
                    $types = [$types];
                }
            } elseif (is_null($types)) {
                $types = ['unknown'];
            } elseif (! is_array($types)) {
                $types = [(string) $types];
            }

            foreach ($types as $type) {
                $cleanType = trim($type, "\" \t\n\r\0\x0B");
                if (! isset($hazardTypes[$cleanType])) {
                    $hazardTypes[$cleanType] = 0;
                }
                $hazardTypes[$cleanType]++;
            }
        }

        $hazardTypeSummary = collect($hazardTypes)->map(function ($count, $type) use ($total) {
            return [
                'hazardType' => $type,
                'count'      => $count,
                'percentage' => $total ? round(($count / $total) * 100, 2) : 0,
            ];
        })->values();

        $ghsClassifications = [];
        foreach ($records as $record) {
            $classification = $record->ghsClassification;

            if (is_array($classification) || is_object($classification)) {
                $classification = json_encode($classification);
            } elseif (is_null($classification)) {
                $classification = 'unknown';
            } else {
                $classification = (string) $classification;
            }

            if (! isset($ghsClassifications[$classification])) {
                $ghsClassifications[$classification] = 0;
            }
            $ghsClassifications[$classification]++;
        }

        $ghsClassificationSummary = collect($ghsClassifications)->map(function ($count, $classification) use ($total) {
            return [
                'ghsClassification' => $classification,
                'count'             => $count,
                'percentage'        => $total ? round(($count / $total) * 100, 2) : 0,
            ];
        })->values();

        $zdhcLevels = [];
        foreach ($records as $record) {
            $level = $record->zdhcLevel;

            if (is_array($level) || is_object($level)) {
                $level = json_encode($level);
            } elseif (is_null($level)) {
                $level = 'unknown';
            } else {
                $level = (string) $level;
            }

            if (! isset($zdhcLevels[$level])) {
                $zdhcLevels[$level] = 0;
            }
            $zdhcLevels[$level]++;
        }

        $zdhcLevelSummary = collect($zdhcLevels)->map(function ($count, $level) use ($total) {
            return [
                'zdhcLevel'  => $level,
                'count'      => $count,
                'percentage' => $total ? round(($count / $total) * 100, 2) : 0,
            ];
        })->values();

        return response()->json([
            'startDate'                => $startDate,
            'endDate'                  => $endDate,
            'division'                 => $division,
            'totalRecords'             => $total,
            'hazardTypeSummary'        => $hazardTypeSummary,
            'ghsClassificationSummary' => $ghsClassificationSummary,
            'zdhcLevelSummary'         => $zdhcLevelSummary,
        ]);
    }
    public function getDoYouHaveMsdsPercentage($startDate, $endDate, $division)
    {
        $startDate = \Carbon\Carbon::parse($startDate)->startOfDay();
        $endDate   = \Carbon\Carbon::parse($endDate)->endOfDay();

        $records = $this->purchaseInventoryInterface->filterByParams($startDate, $endDate, $division)
            ->where('status', 'published');

        $totalCount    = $records->count();
        $trueMsdsCount = $records->where('doYouHaveMSDSorSDS', true)->count();
        $percentage    = $totalCount > 0 ? round(($trueMsdsCount / $totalCount) * 100, 2) : 0;

        return response()->json([
            'totalPublishedRecords' => $totalCount,
            'recordsWithMSDS'       => $trueMsdsCount,
            'percentage'            => $percentage,
        ]);
    }

    public function getAllSummary($year)
    {
        $startDate = \Carbon\Carbon::parse("$year-01-01");
        $endDate   = \Carbon\Carbon::parse("$year-12-31");
        $division  = null;

        $purchaseRecords = $this->purchaseInventoryInterface->filterByYear($year);
        $chemicalRecords = $this->chemicalManagementRecodeInterface->filterByYear($year);

        $inStockFormulas       = [];
        $deliveredTotal        = 0;
        $amountTotal           = 0;
        $monthlyAggregated     = [];
        $thresholdChemicals    = [];
        $highestStockChemicals = [];
        $statusCounts          = [];

        $latestRecords = $this->purchaseInventoryInterface
            ->filterByParams($startDate, $endDate, $division)
            ->sortByDesc('updated_at')
            ->values();

        $latestTransactions = $this->purchaseInventoryInterface
            ->filterByParams($startDate, $endDate, $division)
            ->where('status', 'published')
            ->sortByDesc('updated_at')
            ->values();

        $formattedLatestRecords = $latestRecords->map(function ($record) {
            $documents = is_string($record->documents) ? json_decode($record->documents, true) : ($record->documents ?? []);
            foreach ($documents as &$doc) {
                if (isset($doc['gsutil_uri'])) {
                    $urlData         = $this->chemicalManagementService->getImageUrl($doc['gsutil_uri']);
                    $doc['imageUrl'] = $urlData['signedUrl'];
                    $doc['fileName'] = $urlData['fileName'];
                }
            }
            $record->documents = $documents;

            $certificates = collect($this->certificateRecordInterface->findByInventoryId($record->id))->map(function ($cert) {
                $docs = is_string($cert->documents) ? json_decode($cert->documents, true) : ($cert->documents ?? []);
                foreach ($docs as &$doc) {
                    if (isset($doc['gsutil_uri'])) {
                        $urlData         = $this->certificationRecodeService->getImageUrl($doc['gsutil_uri']);
                        $doc['imageUrl'] = $urlData['signedUrl'];
                        $doc['fileName'] = $urlData['fileName'];
                    }
                }
                $cert->documents = $docs;
                return $cert;
            });

            $record->certificate     = $certificates;
            $record->createdByUser   = $this->getUserInfo($record->createdByUser);
            $record->updatedByUser   = $this->getUserInfo($record->updatedBy);
            $record->approvedByUser  = $this->getUserInfo($record->approverId);
            $record->publishedByUser = $this->getUserInfo($record->publishedBy);

            return $record;
        });

        $formattedTransactions = $latestTransactions->map(function ($record) {
            $documents = is_string($record->documents) ? json_decode($record->documents, true) : ($record->documents ?? []);
            foreach ($documents as &$doc) {
                if (isset($doc['gsutil_uri'])) {
                    $urlData         = $this->chemicalManagementService->getImageUrl($doc['gsutil_uri']);
                    $doc['imageUrl'] = $urlData['signedUrl'];
                    $doc['fileName'] = $urlData['fileName'];
                }
            }
            $record->documents = $documents;

            $certificates = collect($this->certificateRecordInterface->findByInventoryId($record->id))->map(function ($cert) {
                $docs = is_string($cert->documents) ? json_decode($cert->documents, true) : ($cert->documents ?? []);
                foreach ($docs as &$doc) {
                    if (isset($doc['gsutil_uri'])) {
                        $urlData         = $this->certificationRecodeService->getImageUrl($doc['gsutil_uri']);
                        $doc['imageUrl'] = $urlData['signedUrl'];
                        $doc['fileName'] = $urlData['fileName'];
                    }
                }
                $cert->documents = $docs;
                return $cert;
            });

            $record->certificate     = $certificates;
            $record->createdByUser   = $this->getUserInfo($record->createdByUser);
            $record->updatedByUser   = $this->getUserInfo($record->updatedBy);
            $record->approvedByUser  = $this->getUserInfo($record->approverId);
            $record->publishedByUser = $this->getUserInfo($record->publishedBy);

            return $record;
        });

        $insights        = $this->getChemicalInventoryInsights($startDate, $endDate, $division)->getData(true);
        $categorySummary = $this->getCategoryAndClassification($startDate, $endDate, $division)->getData(true);

        foreach ($purchaseRecords as $record) {
            if (! empty($record->molecularFormula)) {
                $inStockFormulas[$record->molecularFormula] = true;
            }

            if ($record->status === 'published') {
                if (is_numeric($record->deliveryQuantity)) {
                    $qty = floatval($record->deliveryQuantity);
                    $deliveredTotal += $qty;

                    if (is_numeric($record->thresholdLimit)) {
                        $formula   = $record->molecularFormula;
                        $threshold = floatval($record->thresholdLimit);
                        if (! isset($thresholdChemicals[$formula])) {
                            $thresholdChemicals[$formula] = ['chemicalName' => $formula, 'totalLimit' => 0, 'totalQuantity' => 0];
                        }
                        $thresholdChemicals[$formula]['totalLimit'] += $threshold;
                        $thresholdChemicals[$formula]['totalQuantity'] += $qty;
                    }

                    if (! isset($highestStockChemicals[$formula])) {
                        $highestStockChemicals[$formula] = ['chemicalName' => $formula, 'totalQuantity' => 0];
                    }
                    $highestStockChemicals[$formula]['totalQuantity'] += $qty;
                }

                if (is_numeric($record->purchaseAmount)) {
                    $amountTotal += floatval($record->purchaseAmount);
                }

                if (! empty($record->deliveryDate) && ! empty($record->molecularFormula)) {
                    $deliveryDate = \Carbon\Carbon::parse($record->deliveryDate);
                    if ($deliveryDate->between($startDate, $endDate)) {
                        $monthName = $deliveryDate->format('F');
                        $formula   = $record->molecularFormula;
                        $qty       = is_numeric($record->deliveryQuantity) ? floatval($record->deliveryQuantity) : 0;

                        if (! isset($monthlyAggregated[$monthName][$formula])) {
                            $monthlyAggregated[$monthName][$formula] = 0;
                        }
                        $monthlyAggregated[$monthName][$formula] += $qty;
                    }
                }
            }

            $status = $record->status ?? 'unknown';
            if (! isset($statusCounts[$status])) {
                $statusCounts[$status] = 0;
            }
            $statusCounts[$status]++;
        }

        foreach ($chemicalRecords as $record) {
            $status = $record->status ?? 'unknown';
            if (! isset($statusCounts[$status])) {
                $statusCounts[$status] = 0;
            }
            $statusCounts[$status]++;
        }

        foreach ($thresholdChemicals as &$data) {
            $data['percentage'] = $data['totalLimit'] > 0
            ? round(($data['totalQuantity'] / $data['totalLimit']) * 100, 2)
            : 0;
        }

        $monthlyBreakdown = [];
        foreach ($monthlyAggregated as $month => $formulas) {
            foreach ($formulas as $formula => $qty) {
                $monthlyBreakdown[] = [
                    'month'    => $month,
                    'chemical' => $formula,
                    'quantity' => $qty,
                ];
            }
        }

        $statusSummary = [];
        foreach ($statusCounts as $status => $count) {
            $statusSummary[] = ['status' => $status, 'count' => $count];
        }
        $msdsRecords = $this->purchaseInventoryInterface->filterByParams($startDate, $endDate, $division)
            ->where('status', 'published');

        $msdsTotal   = $msdsRecords->count();
        $msdsTrue    = $msdsRecords->where('doYouHaveMSDSorSDS', true)->count();
        $msdsPercent = $msdsTotal > 0 ? round(($msdsTrue / $msdsTotal) * 100, 2) : 0;

        $msdsSummary = [
            'totalPublishedRecords' => $msdsTotal,
            'recordsWithMSDS'       => $msdsTrue,
            'percentage'            => $msdsPercent,
        ];

        return response()->json([
            'year'                          => $year,
            'inStockCount'                  => count($inStockFormulas),
            'deliveredTotal'                => $deliveredTotal,
            'purchaseAmount'                => round($amountTotal, 2),
            'monthlyDelivery'               => $monthlyBreakdown,
            'stockThreshold'                => array_values($thresholdChemicals),
            'highestStock'                  => array_values($highestStockChemicals),
            'statusSummary'                 => $statusSummary,
            'latestRecords'                 => $formattedLatestRecords->values(),
            'latestTransactions'            => $formattedTransactions->values(),
            'chemicalInventoryInsights'     => $insights,
            'categoryClassificationSummary' => $categorySummary,
            'msdsSummary'                   => $msdsSummary,
        ]);
    }

}
