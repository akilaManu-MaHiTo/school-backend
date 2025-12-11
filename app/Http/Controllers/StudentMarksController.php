<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentMarks\StudentMarksRequest;
use App\Models\StudentMarks;
use App\Repositories\All\StudentMarks\StudentMarksInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class StudentMarksController extends Controller
{
    public function __construct(private readonly StudentMarksInterface $studentMarksInterface) {}

    public function index(): JsonResponse
    {
        $marks = $this->studentMarksInterface
            ->all(['*'], ['studentProfile.student', 'subject'], 'created_at', 'desc')
            ->map(fn(StudentMarks $mark) => $this->formatMark($mark));

        return response()->json([
            'success' => true,
            'data'    => $marks,
        ]);
    }

    public function store(StudentMarksRequest $request): JsonResponse
    {
        $payload = $request->validated();

        try {
            $mark = $this->studentMarksInterface->create($payload);
        } catch (\Throwable $throwable) {
            Log::error('StudentMarksController.store failed', [
                'payload' => $payload,
                'error'   => $throwable->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create student mark.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Student mark created successfully.',
            'data'    => $this->formatMark($mark),
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        try {
            $mark = $this->studentMarksInterface->findById($id, ['*'], ['studentProfile.student', 'subject']);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Student mark not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->formatMark($mark),
        ]);
    }

    public function update(StudentMarksRequest $request, int $id): JsonResponse
    {
        try {
            $this->studentMarksInterface->findById($id);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Student mark not found.',
            ], 404);
        }

        $payload = $request->validated();

        $this->studentMarksInterface->update($id, $payload);

        $mark = $this->studentMarksInterface->findById($id, ['*'], ['studentProfile.student', 'subject']);

        return response()->json([
            'success' => true,
            'message' => 'Student mark updated successfully.',
            'data'    => $this->formatMark($mark),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->studentMarksInterface->findById($id);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Student mark not found.',
            ], 404);
        }

        $deleted = $this->studentMarksInterface->deleteById($id);

        return response()->json([
            'success' => $deleted,
            'message' => $deleted ? 'Student mark deleted successfully.' : 'Failed to delete student mark.',
        ], $deleted ? 200 : 500);
    }

    private function formatMark(StudentMarks $mark): array
    {
        $mark->loadMissing(['studentProfile.student', 'subject']);

        return [
            'id'                => $mark->id,
            'studentProfileId'  => $mark->studentProfileId,
            'academicSubjectId' => $mark->academicSubjectId,
            'studentMark'       => $mark->studentMark,
            'markGrade'         => $mark->markGrade,
            'academicYear'      => $mark->academicYear,
            'academicTerm'      => $mark->academicTerm,
            'studentProfile'    => $mark->studentProfile ? [
                'id'             => $mark->studentProfile->id,
                'academicYear'   => $mark->studentProfile->academicYear,
                'academicMedium' => $mark->studentProfile->academicMedium,
                'student'        => $mark->studentProfile->student ? [
                    'id'    => $mark->studentProfile->student->id,
                    'name'  => $mark->studentProfile->student->name,
                    'email' => $mark->studentProfile->student->email,
                ] : null,
            ] : null,
            'subject'           => $mark->subject ? [
                'id'          => $mark->subject->id,
                'subjectCode' => $mark->subject->subjectCode,
                'subjectName' => $mark->subject->subjectName,
            ] : null,
            'createdAt'         => $mark->created_at,
            'updatedAt'         => $mark->updated_at,
        ];
    }
}
