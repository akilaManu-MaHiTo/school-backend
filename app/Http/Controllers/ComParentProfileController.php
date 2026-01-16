<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComParentProfile\ComParentProfileStoreRequest;
use App\Http\Requests\ComParentProfile\ComParentProfileUpdateRequest;
use App\Repositories\All\ComParentProfile\ComParentProfileInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ComParentProfileController extends Controller
{
    public function __construct(private readonly ComParentProfileInterface $parentProfileInterface) {}

    public function index(): JsonResponse
    {
        $items = $this->parentProfileInterface->all();
        return response()->json($items, 200);
    }

    public function store(ComParentProfileStoreRequest $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $data = $request->validated();
        $userId = $user->id;
        $data['parentId'] = $userId;
        if ($this->parentProfileInterface->isDuplicate($userId, $data['studentId'])) {
            return response()->json([
                'success' => false,
                'message' => 'This parent is already linked to this student profile.',
            ], 409);
        }

        $created = $this->parentProfileInterface->create($data);

        return response()->json($created, 201);
    }

    public function parentProfileCreateByAdmin(ComParentProfileStoreRequest $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $data = $request->validated();
        $userId = $data['parentId'];
        if ($this->parentProfileInterface->isDuplicate($userId, $data['studentId'])) {
            return response()->json([
                'success' => false,
                'message' => 'This parent is already linked to this student profile.',
            ], 409);
        }

        $created = $this->parentProfileInterface->create($data);

        return response()->json($created, 201);
    }

    public function show(int $id): JsonResponse
    {
        $item = $this->parentProfileInterface->getById($id);

        if (! $item) {
            return response()->json(['success' => false, 'message' => 'Parent profile not found.'], 404);
        }

        return response()->json($item, 200);
    }

    public function update(ComParentProfileUpdateRequest $request, int $id): JsonResponse
    {
        $item = $this->parentProfileInterface->getById($id);

        if (! $item) {
            return response()->json(['success' => false, 'message' => 'Parent profile not found.'], 404);
        }

        $data = $request->validated();

        $parentId = $data['parentId'] ?? $item->parentId;
        $studentId = $data['studentId'] ?? $item->studentId;

        if ($this->parentProfileInterface->isDuplicate($parentId, $studentId, $id)) {
            return response()->json([
                'success' => false,
                'message' => 'This parent is already linked to this student profile.',
            ], 409);
        }

        $this->parentProfileInterface->update($id, $data);

        return response()->json([
            'success' => true,
            'message' => 'Parent profile updated successfully.',
            'data' => $this->parentProfileInterface->getById($id),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $item = $this->parentProfileInterface->getById($id);

        if (! $item) {
            return response()->json(['success' => false, 'message' => 'Parent profile not found.'], 404);
        }

        $this->parentProfileInterface->deleteById($id);

        return response()->json([
            'success' => true,
            'message' => 'Parent profile deleted successfully.',
        ], 200);
    }

    public function getMyChildren(int $parentId): JsonResponse
    {
        $parentProfiles = $this->parentProfileInterface->getByColumn(
            ['parentId' => $parentId],
            ['*'],
            ['student.studentProfiles.grade', 'student.studentProfiles.class']
        );

        if (! $parentProfiles || $parentProfiles->isEmpty()) {
            return response()->json([]);
        }

        $children = $parentProfiles
            ->map(function ($parentProfile) {
                $student = $parentProfile->student;

                if (! $student) {
                    return null;
                }

                return [
                    'id'                => $student->id,
                    'name'              => $student->name,
                    'userName'          => $student->userName,
                    'nameWithInitials'  => $student->nameWithInitials,
                    'email'             => $student->email,
                    'employeeType'      => $student->employeeType,
                    'employeeNumber'    => $student->employeeNumber,
                    'mobile'            => $student->mobile,
                    'emailVerifiedAt'   => $student->emailVerifiedAt,
                    'userType'          => $student->userType,
                    'assigneeLevel'     => $student->assigneeLevel,
                    'profileImage'      => $student->profileImage,
                    'availability'      => $student->availability,
                    'gender'            => $student->gender,
                    'birthDate'         => $student->birthDate,
                    'address'           => $student->address,
                    'student_profiles'  => $student->studentProfiles
                        ->map(function ($profile) {
                            $grade = $profile->grade;
                            $class = $profile->class;

                            return [
                                'id'                 => $profile->id,
                                'studentId'          => $profile->studentId,
                                'academicGradeId'    => $profile->academicGradeId,
                                'academicClassId'    => $profile->academicClassId,
                                'academicYear'       => $profile->academicYear,
                                'academicMedium'     => $profile->academicMedium,
                                'basketSubjectsIds'  => $profile->basketSubjectsIds,
                                'isStudentApproved'  => $profile->isStudentApproved,
                                'grade'              => $grade ? [
                                    'id'         => $grade->id,
                                    'grade'      => $grade->grade,
                                ] : null,
                                'class'              => $class ? [
                                    'id'           => $class->id,
                                    'className'    => $class->className,
                                    'classCategory'=> $class->classCategory,
                                ] : null,
                            ];
                        })
                        ->values(),
                ];
            })
            ->filter()
            ->values();

        return response()->json($children, 200);
    }
}
