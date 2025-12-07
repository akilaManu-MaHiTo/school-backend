<?php

namespace App\Http\Controllers\ComTeacherProfile;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComTeacherProfile\ComTeacherProfileRequest;
use App\Repositories\All\ComTeacherProfile\ComTeacherProfileInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ComTeacherProfileController extends Controller
{
    public function __construct(private readonly ComTeacherProfileInterface $teacherProfileInterface) {}


    public function index(): JsonResponse
    {

        $profiles = $this->teacherProfileInterface
            ->all(['*'], ['teacher', 'grade', 'subject', 'class'], 'created_at', 'desc')
            ->map(fn($profile) => $this->formatProfile($profile));

        return response()->json($profiles, 200);
    }



    public function store(ComTeacherProfileRequest $request): JsonResponse
    {
        Log::info('TeacherProfile.store: Incoming request', [
            'payload' => $request->all()
        ]);
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $userId = $user->id;
        $userType = $user->employeeType;

        $data = $request->validated();
        $data['createdByUser'] = $userId;
        $data['teacherId'] = $userId;

        if ($userType === 'Student' || $userType === 'Parent' || $userType === null) {

            return response()->json(['message' => 'Teachers Only Can Create Teacher Profile'], 401);
        }

        if ($this->teacherProfileInterface->isDuplicate($data)) {

            return response()->json([
                'success' => false,
                'message' => 'Teacher Academic Details Already Exists',
            ], 409);
        }

        try {
            $profile = $this->teacherProfileInterface->create($data);
            return response()->json([
                'success' => true,
                'message' => 'Teacher profile created successfully.',
                'data'    => $this->formatProfile($profile),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while creating the teacher profile.',
            ], 500);
        }
    }


    public function show(int $id): JsonResponse
    {
        try {
            $profile = $this->teacherProfileInterface->findById($id, ['*'], ['teacher', 'grade', 'subject', 'class']);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile not found.',
            ], 404);
        }

        return response()->json($this->formatProfile($profile), 200);
    }

    public function update(ComTeacherProfileRequest $request, int $id): JsonResponse
    {
        try {
            $this->teacherProfileInterface->findById($id);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile not found.',
            ], 404);
        }


        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userId = $user->id;
        $data = $request->validated();
        $data['createdByUser'] = $userId;
        $data['teacherId'] = $userId;

        $userType = $user->employeeType;

        if ($userType === 'Student' || $userType === 'Parent' || $userType === null) {

            return response()->json(['message' => 'Teachers Only Can Update Teacher Profile'], 401);
        }

        if ($this->teacherProfileInterface->isDuplicate($data, $id)) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher Academic Details already exists',
            ], 409);
        }

        $this->teacherProfileInterface->update($id, $data);
        $profile = $this->teacherProfileInterface->findById($id, ['*'], ['teacher', 'grade', 'subject', 'class']);

        return response()->json([
            'success' => true,
            'message' => 'Teacher profile updated successfully.',
            'data'    => $this->formatProfile($profile),
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userType = $user->employeeType;

        if ($userType === 'Student' || $userType === 'Parent' || $userType === null) {

            return response()->json(['message' => 'Teachers Only Can Delete Teacher Profile'], 401);
        }
        try {
            $this->teacherProfileInterface->findById($id);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile not found.',
            ], 404);
        }

        $deleted = $this->teacherProfileInterface->deleteById($id);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? 'Teacher profile deleted successfully.' : 'Failed to delete teacher profile.',
        ], $deleted ? 200 : 500);
    }

    private function formatProfile($profile): array
    {
        $profile->loadMissing(['teacher', 'grade', 'subject', 'class']);

        return [
            'id'             => $profile->id,
            'teacher'        => $profile->teacher ? [
                'id'    => $profile->teacher->id,
                'name'  => $profile->teacher->name,
                'email' => $profile->teacher->email,
            ] : null,
            'grade'          => $profile->grade ? [
                'id'    => $profile->grade->id,
                'grade' => $profile->grade->grade,
            ] : null,
            'subject'        => $profile->subject ? [
                'id'          => $profile->subject->id,
                'subjectCode' => $profile->subject->subjectCode,
                'subjectName' => $profile->subject->subjectName,
            ] : null,
            'class'          => $profile->class ? [
                'id'        => $profile->class->id,
                'className' => $profile->class->className,
            ] : null,
            'academicYear'   => $profile->academicYear,
            'academicMedium' => $profile->academicMedium,
            'createdAt'      => $profile->created_at,
            'updatedAt'      => $profile->updated_at,
        ];
    }
}
