<?php

namespace App\Http\Controllers\ComStudentProfileController;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComStudentProfile\ComStudentProfileRequest;
use App\Models\ComStudentProfile;
use App\Repositories\All\ComStudentProfile\ComStudentProfileInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ComStudentProfileController extends Controller
{
    public function __construct(private readonly ComStudentProfileInterface $studentProfileInterface) {}

    public function index(): JsonResponse
    {
        $profiles = $this->studentProfileInterface
            ->all(['*'], ['student', 'grade', 'class'], 'created_at', 'desc')
            ->map(fn($profile) => $this->formatProfile($profile));

        return response()->json($profiles, 200);
    }

    public function store(ComStudentProfileRequest $request): JsonResponse
    {
        Log::info('ComStudentProfileController.store invoked', [
            'payload' => $request->all(),
        ]);

        $user = Auth::user();
        if (! $user) {
            Log::warning('ComStudentProfileController.store unauthorized access attempt');
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $userId = $user->id;
        Log::info('ComStudentProfileController.store authenticated user', ['userId' => $userId]);

        $data = $request->validated();
        Log::info('ComStudentProfileController.store validation passed');
        $data['studentId'] = $userId;

        if ($this->studentProfileInterface->isDuplicate($data)) {
            Log::warning('ComStudentProfileController.store duplicate detected', [
                'studentId' => $userId,
                'attributes' => $data,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Student academic profile already exists.',
            ], 409);
        }

        try {
            $profile = $this->studentProfileInterface->create($data);
        } catch (\Throwable $throwable) {
            Log::error('ComStudentProfileController.store failed', [
                'payload' => $data,
                'error'   => $throwable->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create student profile.',
            ], 500);
        }

        Log::info('ComStudentProfileController.store completed successfully', [
            'studentProfileId' => $profile->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Student profile created successfully.',
            'data'    => $this->formatProfile($profile),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $profile = $this->studentProfileInterface->findById($id, ['*'], ['student', 'grade', 'class']);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found.',
            ], 404);
        }

        return response()->json($this->formatProfile($profile), 200);
    }

    public function update(ComStudentProfileRequest $request, int $id): JsonResponse
    {
        try {
            $this->studentProfileInterface->findById($id);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found.',
            ], 404);
        }

        $data = $request->validated();

        if ($this->studentProfileInterface->isDuplicate($data, $id)) {
            return response()->json([
                'success' => false,
                'message' => 'Student academic profile already exists.',
            ], 409);
        }

        $this->studentProfileInterface->update($id, $data);
        $profile = $this->studentProfileInterface->findById($id, ['*'], ['student', 'grade', 'class']);

        return response()->json([
            'success' => true,
            'message' => 'Student profile updated successfully.',
            'data'    => $this->formatProfile($profile),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->studentProfileInterface->findById($id);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found.',
            ], 404);
        }

        $deleted = $this->studentProfileInterface->deleteById($id);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? 'Student profile deleted successfully.' : 'Failed to delete student profile.',
        ], $deleted ? 200 : 500);
    }

    private function formatProfile(ComStudentProfile $profile): array
    {
        $profile->loadMissing(['student', 'grade', 'class']);

        return [
            'id'             => $profile->id,
            'student'        => $profile->student ? [
                'id'    => $profile->student->id,
                'name'  => $profile->student->name,
                'email' => $profile->student->email,
            ] : null,
            'grade'          => $profile->grade ? [
                'id'    => $profile->grade->id,
                'grade' => $profile->grade->grade,
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
