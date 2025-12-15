<?php

namespace App\Http\Controllers\ComStudentProfileController;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComStudentProfile\ComStudentProfileRequest;
use App\Models\ComStudentProfile;
use App\Models\ComSubjects;
use App\Models\StudentMarks;
use App\Repositories\All\ComStudentProfile\ComStudentProfileInterface;
use App\Traits\HandlesBasketSubjects;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class ComStudentProfileController extends Controller
{
    use HandlesBasketSubjects;
    public function __construct(private readonly ComStudentProfileInterface $studentProfileInterface) {}

    public function index(): JsonResponse
    {
        $profiles = $this->studentProfileInterface
            ->all(['*'], ['student', 'grade', 'class'], 'created_at', 'desc');

        $basketSubjectsLookup = $this->fetchBasketSubjects(
            $profiles
                ->flatMap(fn($profile) => $this->normalizeBasketSubjectIds($profile->basketSubjectsIds ?? null))
                ->unique()
                ->values()
                ->all()
        );

        $payload = $profiles->map(fn($profile) => $this->formatProfile($profile, $basketSubjectsLookup));

        return response()->json($payload, 200);
    }

    public function store(ComStudentProfileRequest $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $userId = $user->id;
        $data = $request->validated();
        $data['studentId'] = $userId;
        $basketSubjectArray = array_map('intval', $data['basketSubjectsIds'] ?? []);
        $data['basketSubjectsIds'] = $basketSubjectArray;

        if (! empty($basketSubjectArray)) {
            $existingSubjects = ComSubjects::query()
                ->whereIn('id', $basketSubjectArray)
                ->pluck('id')
                ->all();

            $missingSubjects = array_values(array_diff($basketSubjectArray, $existingSubjects));

            if (! empty($missingSubjects)) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more basket subjects do not exist.',
                    'invalidSubjectIds' => $missingSubjects,
                ], 422);
            }
        }
        if ($this->studentProfileInterface->isDuplicate($data)) {

            return response()->json([
                'success' => false,
                'message' => 'Student Assigned ALready to this Class',
            ], 409);
        }
        try {
            $profile = $this->studentProfileInterface->create($data);
        } catch (\Throwable $throwable) {


            return response()->json([
                'success' => false,
                'message' => 'Failed to create student profile.',
            ], 500);
        }

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

        $basketSubjectArray = array_map('intval', $data['basketSubjectsIds'] ?? []);
        $data['basketSubjectsIds'] = $basketSubjectArray;

        if (! empty($basketSubjectArray)) {
            $existingSubjects = ComSubjects::query()
                ->whereIn('id', $basketSubjectArray)
                ->pluck('id')
                ->all();

            $missingSubjects = array_values(array_diff($basketSubjectArray, $existingSubjects));

            if (! empty($missingSubjects)) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more basket subjects do not exist.',
                    'invalidSubjectIds' => $missingSubjects,
                ], 422);
            }
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

    private function formatProfile(ComStudentProfile $profile, ?Collection $subjectLookup = null): array
    {
        $profile->loadMissing(['student', 'grade', 'class']);
        $basketSubjectIds = $this->normalizeBasketSubjectIds($profile->basketSubjectsIds ?? null);
        $lookup = $subjectLookup ?? $this->fetchBasketSubjects($basketSubjectIds);

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
            'basketSubjectsIds' => $basketSubjectIds,
            'basketSubjects' => $this->formatBasketSubjects($basketSubjectIds, $lookup),
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
            return response()->json([]);
        }

        // Filter students to the basket if the subject is elective; otherwise fall back to the full class list
        $basketSubjectProfiles = $profiles->filter(function (ComStudentProfile $profile) use ($subjectId) {
            $basketSubjectArray = array_map('intval', $profile->basketSubjectsIds ?? []);

            return in_array($subjectId, $basketSubjectArray, true);
        });

        $profilesForSubject = $basketSubjectProfiles->isNotEmpty()
            ? $basketSubjectProfiles
            : $profiles;

        $marks = StudentMarks::query()
            ->whereIn('studentProfileId', $profilesForSubject->pluck('id'))
            ->where('academicSubjectId', $subjectId)
            ->where('academicTerm', $term)
            ->where('academicYear', $year)
            ->get()
            ->keyBy('studentProfileId');

        $payload = $profilesForSubject->map(function (ComStudentProfile $profile) use ($marks, $subject, $term) {
            $mark = $marks->get($profile->id);

            return [
                'studentProfileId'  => $profile->id,
                'student'           => $profile->student ? [
                    'id'    => $profile->student->id,
                    'employeeNumber' => $profile->student->employeeNumber,
                    'userName' => $profile->student->userName,
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
                'markId'            => $mark?->id,
                'isAbsentStudent'   => $mark?->isAbsentStudent
            ];
        })->values();

        return response()->json(
            $payload,
        );
    }
}
