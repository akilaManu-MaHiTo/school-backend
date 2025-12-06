<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Notifications\EmailChangeOTPsend\SendOtpEmailChange;
use App\Repositories\All\AssigneeLevel\AssigneeLevelInterface;
use App\Repositories\All\ComOrganization\ComOrganizationInterface;
use App\Repositories\All\ComPermission\ComPermissionInterface;
use App\Repositories\All\User\UserInterface;
use App\Services\OrganizationService;
use App\Services\ProfileImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected $userInterface;

    protected $comPermissionInterface;

    protected $assigneeLevelInterface;

    protected $profileImageService;

    protected $organizationService;

    protected $comOrganizationInterface;

    public function __construct(UserInterface $userInterface, ComPermissionInterface $comPermissionInterface, AssigneeLevelInterface $assigneeLevelInterface, ProfileImageService $profileImageService, ComOrganizationInterface $comOrganizationInterface, OrganizationService $organizationService)
    {
        $this->userInterface = $userInterface;
        $this->comPermissionInterface = $comPermissionInterface;
        $this->assigneeLevelInterface = $assigneeLevelInterface;
        $this->profileImageService = $profileImageService;
        $this->organizationService = $organizationService;
        $this->comOrganizationInterface = $comOrganizationInterface;
    }

    public function show(Request $request)
    {
        $user = $request->user();

        if (! $user || $user->availability != 1) {
            return response()->json(['message' => 'User not available'], 403);
        }

        $permission = $this->comPermissionInterface->getById($user->userType);
        $userData = $user->toArray();

        $profileImages = is_array($user->profileImage) ? $user->profileImage : json_decode($user->profileImage, true) ?? [];

        $signedImages = [];
        foreach ($profileImages as $uri) {
            $signed = $this->profileImageService->getImageUrl($uri);
            $signedImages[] = [
                'fileName' => $signed['fileName'] ?? null,
                'imageUrl' => $signed['signedUrl'] ?? null,
            ];
        }

        foreach ($profileImages as &$uri) {
            if (isset($document['gsutil_uri'])) {
                $imageData = $this->profileImageService->getImageUrl($document['gsutil_uri']);
                $document['imageUrl'] = $imageData['signedUrl'];
                $document['fileName'] = $imageData['fileName'];
            }
        }

        $userData['profileImage'] = $signedImages;

        if ($permission) {
            $userData['permissionObject'] = (array) $permission->permissionObject;
            $userData['userType'] = [
                'id' => $permission->id,
                'name' => $permission->userType,
                'description' => $permission->description,
            ];
            $userData['userTypeObject'] = [
                'id' => $permission->id,
                'name' => $permission->userType ?? null,
                'description' => $permission->description ?? null,
            ];
        }

        $userData['userLevel'] = $this->assigneeLevelInterface->getById($user->assigneeLevel);

        $userData['assigneeLevelObject'] = $this->assigneeLevelInterface->getById($user->assigneeLevel);

        return response()->json($userData, 200);
    }

    public function index()
    {
        $users = $this->userInterface->All();

        $userData = $users->map(function ($user) {
            $userArray = $user->toArray();

            $permission = $this->comPermissionInterface->getById($user->userType);
            $userArray['userType'] = [
                'id' => $permission->id ?? null,
                'userType' => $permission->userType ?? null,
                'description' => $permission->description ?? null,
            ];

            $assigneeLevel = $this->assigneeLevelInterface->getById($user->assigneeLevel);
            $userArray['userLevel'] = $assigneeLevel ? [
                'id' => $assigneeLevel->id,
                'levelId' => $assigneeLevel->levelId,
                'levelName' => $assigneeLevel->levelName,
            ] : [];

            $profileImages = is_array($user->profileImage) ? $user->profileImage : json_decode($user->profileImage, true) ?? [];
            $signedImages = [];

            foreach ($profileImages as $uri) {
                $signed = $this->profileImageService->getImageUrl($uri);
                $signedImages[] = [
                    'fileName' => $signed['fileName'] ?? null,
                    'imageUrl' => $signed['signedUrl'] ?? null,
                ];
            }

            $userArray['profileImage'] = $signedImages;

            return $userArray;
        });

        return response()->json($userData, 200);
    }

    public function changePassword(Request $request)
    {
        $user = $this->userInterface->getById($request->user()->id);

        $validator = Validator::make($request->all(), [
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (! Hash::check($request->currentPassword, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        $user->password = Hash::make($request->newPassword);

        $user->save();

        return response()->json(['message' => 'Password changed successfully'], 200);
    }

    public function profileUpdate(ProfileUpdateRequest $request, $id)
    {
        $user = $this->userInterface->getById($id);

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $existingImages = is_array($user->profileImage)
            ? $user->profileImage
            : json_decode($user->profileImage, true) ?? [];

        if ($request->filled('removeDoc')) {
            foreach ($request->removeDoc as $removeDoc) {
                $this->profileImageService->deleteImageFromGCS($removeDoc);
                $existingImages = array_filter($existingImages, fn($img) => $img !== $removeDoc);
            }
        }

        if ($request->hasFile('profileImage')) {
            foreach ($request->file('profileImage') as $file) {
                $uploadResult = $this->profileImageService->uploadImageToGCS($file);
                if ($uploadResult && isset($uploadResult['gsutil_uri'])) {
                    $existingImages[] = $uploadResult['gsutil_uri'];
                }
            }
        }

        $user->name = $request->input('name', $user->name);
        $user->mobile = $request->input('mobile', $user->mobile);
        $user->gender = $request->input('gender', $user->gender);
        $user->profileImage = $existingImages;

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
        ], 200);
    }

    public function emailChangeInitiate(Request $request, $id)
    {
        $request->validate([
            'currentEmail' => 'required|email|exists:users,email',
        ]);

        $user = $this->userInterface->findById($id);

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->email !== $request->currentEmail) {
            return response()->json(['message' => 'Current email does not match this user.'], 400);
        }

        $otp = rand(100000, 999999);
        $user->otp = $otp;
        $user->otp_expires_at = now()->addMinutes(5);
        $user->save();

        try {
            $organization = $this->comOrganizationInterface->first();

            if ($organization) {
                $organizationName = $organization->organizationName;
                $organizationFactoryName = $organization->organizationFactoryName;
                $logoData = null;

                if (! empty($organization->logoUrl)) {
                    $logoInfo = $this->organizationService->getImageUrl($organization->logoUrl);
                    $logoData = $logoInfo['signedUrl'] ?? null;
                }
                Notification::route('mail', $user->email)->notify(new SendOtpEmailChange($otp, $user->email, $user->name, $organizationName, $logoData, $organizationFactoryName));

                return response()->json(['message' => 'OTP has been sent to your email.'], 201);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send OTP. Please try again later.'], 500);
        }
    }

    public function emailChangeVerify(Request $request, $id)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $user = $this->userInterface->findById($id);

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->otp !== $request->otp) {
            return response()->json(['message' => 'Invalid OTP.'], 400);
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['message' => 'OTP has expired.'], 400);
        }

        return response()->json(['message' => 'OTP verified successfully. Proceed to change email.'], 200);
    }

    public function emailChangeConfirm(Request $request, $id)
    {
        $request->validate([
            'newEmail' => 'required|email|unique:users,email',
        ]);

        $user = $this->userInterface->findById($id);

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->email = $request->newEmail;
        $user->otp = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json(['message' => 'Email changed successfully.'], 200);
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword');

        $users = $this->userInterface->search($keyword);

        $userData = $users->map(function ($user) {
            $userArray = $user->toArray();

            $permission = $this->comPermissionInterface->getById($user->userType);
            $userArray['userType'] = [
                'id' => $permission->id ?? null,
                'userType' => $permission->userType ?? null,
                'description' => $permission->description ?? null,
            ];

            $assigneeLevel = $this->assigneeLevelInterface->getById($user->assigneeLevel);
            $userArray['userLevel'] = $assigneeLevel ? [
                'id' => $assigneeLevel->id,
                'levelId' => $assigneeLevel->levelId,
                'levelName' => $assigneeLevel->levelName,
            ] : [];

            $profileImages = is_array($user->profileImage) ? $user->profileImage : json_decode($user->profileImage, true) ?? [];
            $signedImages = [];

            foreach ($profileImages as $uri) {
                $signed = $this->profileImageService->getImageUrl($uri);
                $signedImages[] = [
                    'fileName' => $signed['fileName'] ?? null,
                    'imageUrl' => $signed['signedUrl'] ?? null,
                ];
            }

            $userArray['profileImage'] = $signedImages;

            return $userArray;
        });

        return response()->json($userData, 200);
    }

    public function searchTeacher(Request $request)
    {
        $keyword = $request->input('keyword');

        $users = $this->userInterface->searchTeachers($keyword);

        $userData = $users->map(function ($user) {
            $userArray = $user->toArray();

            $permission = $this->comPermissionInterface->getById($user->userType);
            $userArray['userType'] = [
                'id' => $permission->id ?? null,
                'userType' => $permission->userType ?? null,
                'description' => $permission->description ?? null,
            ];

            $assigneeLevel = $this->assigneeLevelInterface->getById($user->assigneeLevel);
            $userArray['userLevel'] = $assigneeLevel ? [
                'id' => $assigneeLevel->id,
                'levelId' => $assigneeLevel->levelId,
                'levelName' => $assigneeLevel->levelName,
            ] : [];

            $profileImages = is_array($user->profileImage) ? $user->profileImage : json_decode($user->profileImage, true) ?? [];
            $signedImages = [];

            foreach ($profileImages as $uri) {
                $signed = $this->profileImageService->getImageUrl($uri);
                $signedImages[] = [
                    'fileName' => $signed['fileName'] ?? null,
                    'imageUrl' => $signed['signedUrl'] ?? null,
                ];
            }

            $userArray['profileImage'] = $signedImages;

            return $userArray;
        });

        return response()->json($userData, 200);
    }
    public function searchParent(Request $request)
    {
        $keyword = $request->input('keyword');

        $users = $this->userInterface->searchParents($keyword);

        $userData = $users->map(function ($user) {
            $userArray = $user->toArray();

            $permission = $this->comPermissionInterface->getById($user->userType);
            $userArray['userType'] = [
                'id' => $permission->id ?? null,
                'userType' => $permission->userType ?? null,
                'description' => $permission->description ?? null,
            ];

            $assigneeLevel = $this->assigneeLevelInterface->getById($user->assigneeLevel);
            $userArray['userLevel'] = $assigneeLevel ? [
                'id' => $assigneeLevel->id,
                'levelId' => $assigneeLevel->levelId,
                'levelName' => $assigneeLevel->levelName,
            ] : [];

            $profileImages = is_array($user->profileImage) ? $user->profileImage : json_decode($user->profileImage, true) ?? [];
            $signedImages = [];

            foreach ($profileImages as $uri) {
                $signed = $this->profileImageService->getImageUrl($uri);
                $signedImages[] = [
                    'fileName' => $signed['fileName'] ?? null,
                    'imageUrl' => $signed['signedUrl'] ?? null,
                ];
            }

            $userArray['profileImage'] = $signedImages;

            return $userArray;
        });

        return response()->json($userData, 200);
    }
    public function searchStudent(Request $request)
    {
        $keyword = $request->input('keyword');

        $users = $this->userInterface->searchStudents($keyword);

        $userData = $users->map(function ($user) {
            $userArray = $user->toArray();

            $permission = $this->comPermissionInterface->getById($user->userType);
            $userArray['userType'] = [
                'id' => $permission->id ?? null,
                'userType' => $permission->userType ?? null,
                'description' => $permission->description ?? null,
            ];

            $assigneeLevel = $this->assigneeLevelInterface->getById($user->assigneeLevel);
            $userArray['userLevel'] = $assigneeLevel ? [
                'id' => $assigneeLevel->id,
                'levelId' => $assigneeLevel->levelId,
                'levelName' => $assigneeLevel->levelName,
            ] : [];

            $profileImages = is_array($user->profileImage) ? $user->profileImage : json_decode($user->profileImage, true) ?? [];
            $signedImages = [];

            foreach ($profileImages as $uri) {
                $signed = $this->profileImageService->getImageUrl($uri);
                $signedImages[] = [
                    'fileName' => $signed['fileName'] ?? null,
                    'imageUrl' => $signed['signedUrl'] ?? null,
                ];
            }

            $userArray['profileImage'] = $signedImages;

            return $userArray;
        });

        return response()->json($userData, 200);
    }
    public function searchStaff(Request $request)
    {
        $keyword = $request->input('keyword');

        $users = $this->userInterface->searchStaffs($keyword);

        $userData = $users->map(function ($user) {
            $userArray = $user->toArray();

            $permission = $this->comPermissionInterface->getById($user->userType);
            $userArray['userType'] = [
                'id' => $permission->id ?? null,
                'userType' => $permission->userType ?? null,
                'description' => $permission->description ?? null,
            ];

            $assigneeLevel = $this->assigneeLevelInterface->getById($user->assigneeLevel);
            $userArray['userLevel'] = $assigneeLevel ? [
                'id' => $assigneeLevel->id,
                'levelId' => $assigneeLevel->levelId,
                'levelName' => $assigneeLevel->levelName,
            ] : [];

            $profileImages = is_array($user->profileImage) ? $user->profileImage : json_decode($user->profileImage, true) ?? [];
            $signedImages = [];

            foreach ($profileImages as $uri) {
                $signed = $this->profileImageService->getImageUrl($uri);
                $signedImages[] = [
                    'fileName' => $signed['fileName'] ?? null,
                    'imageUrl' => $signed['signedUrl'] ?? null,
                ];
            }

            $userArray['profileImage'] = $signedImages;

            return $userArray;
        });

        return response()->json($userData, 200);
    }
}
