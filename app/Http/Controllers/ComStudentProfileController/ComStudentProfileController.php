<?php

namespace App\Http\Controllers\ComStudentProfileController;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComStudentProfile\ComStudentProfileRequest;
use App\Models\ComStudentProfile;
use App\Models\ComSubjects;
use App\Models\StudentMarks;
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
                'message' => 'Student Assigned ALready to this Class',
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
            $profile = $this->studentProfileInterface->findById($id);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found.',
            ], 404);
        }

        $data = $request->validated();

        if (! array_key_exists('studentId', $data)) {
            $data['studentId'] = $profile->studentId;
        }

        if ($this->studentProfileInterface->isDuplicate($data, $id)) {
            return response()->json([
                'success' => false,
                'message' => 'Student Assigned ALready to this Class',
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

    public function getStudentMarks(
        int $gradeId,
        int $classId,
        string $year,
        string $medium,
        int $subjectId,
        string $term,

    ): JsonResponse {
        $subject = ComSubjects::find($subjectId);

        if (! $subject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found.',
            ], 404);
        }

        $profiles = ComStudentProfile::query()
            ->with(['student', 'grade', 'class'])
            ->where('academicGradeId', $gradeId)
            ->where('academicClassId', $classId)
            ->where('academicMedium', $medium)
            ->where('academicYear', $year)
            ->get();

        if ($profiles->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No student profiles found for the provided criteria.',
                'data'    => [],
                'subject' => [
                    'id'          => $subject->id,
                    'subjectCode' => $subject->subjectCode,
                    'subjectName' => $subject->subjectName,
                ],
            ]);
        }

        $marks = StudentMarks::query()
            ->whereIn('studentProfileId', $profiles->pluck('id'))
            ->where('academicSubjectId', $subjectId)
            ->where('academicTerm', $term)
            ->where('academicYear', $year)
            ->get()
            ->keyBy('studentProfileId');

        $payload = $profiles->map(function (ComStudentProfile $profile) use ($marks, $subject, $term) {
            $mark = $marks->get($profile->id);

            return [
                'studentProfileId'  => $profile->id,
                'student'           => $profile->student ? [
                    'id'    => $profile->student->id,
                    'userName'=> $profile->student->userName,
                    'name'  => $profile->student->name,
                    'email' => $profile->student->email,
                ] : null,
                'grade'             => $profile->grade ? [
                    'id'    => $profile->grade->id,
                    'grade' => $profile->grade->grade,
                ] : null,
                'class'             => $profile->class ? [
                    'id'        => $profile->class->id,
                    'className' => $profile->class->className,
                ] : null,

                'academicYear'      => $profile->academicYear,
                'academicMedium'    => $profile->academicMedium,
                'subject'           => [
                    'id'          => $subject->id,
                    'subjectCode' => $subject->subjectCode,
                    'subjectName' => $subject->subjectName,
                ],
                'academicTerm'      => $term,
                'studentMark'       => $mark?->studentMark,
                'markGrade'         => $mark?->markGrade,
            ];
        })->values();

        return response()->json(
            $payload,
        );
    }
}
