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
            Log::warning('TeacherProfile.store: Unauthorized request');
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        Log::info('TeacherProfile.store: Authenticated user', [
            'userId' => $user->id,
            'email'  => $user->email,
        ]);

        $userId = $user->id;

        // Get validated data
        $data = $request->validated();
        $data['createdByUser'] = $userId;
        $data['teacherId'] = $userId;

        Log::info('TeacherProfile.store: Validated + prepared data', [
            'data' => $data
        ]);

        // Check duplicate
        if ($this->teacherProfileInterface->isDuplicate($data)) {
            Log::warning('TeacherProfile.store: Duplicate profile detected', [
                'teacherId' => $userId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Teacher profile already exists for the given combination.',
            ], 409);
        }

        try {
            Log::info('TeacherProfile.store: Creating profile');

            $profile = $this->teacherProfileInterface->create($data);

            Log::info('TeacherProfile.store: Profile created successfully', [
                'profileId' => $profile->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Teacher profile created successfully.',
                'data'    => $this->formatProfile($profile),
            ], 201);
        } catch (\Exception $e) {
            Log::error('TeacherProfile.store: Error creating profile', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data
            ]);

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

        $data = $request->validated();

        if ($this->teacherProfileInterface->isDuplicate($data, $id)) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher profile already exists for the given combination.',
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
