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

    public function getTeacherYears(): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userType = $user->employeeType;

        if ($userType === 'Student' || $userType === 'Parent' || $userType === null) {
            return response()->json(['message' => 'Teachers Only Can View Teacher Years'], 401);
        }

        $years = $this->teacherProfileInterface->getTeacherYears($user->id);

        return response()->json(
            $years,
            200
        );
    }

    public function getTeacherClasses(string $year, string $gradeId): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (! $year || ! $gradeId) {
            return response()->json(['message' => 'Academic year and grade are required'], 400);
        }

        $userType = $user->employeeType;

        if ($userType === 'Student' || $userType === 'Parent' || $userType === null) {
            return response()->json(['message' => 'Teachers Only Can View Classes'], 401);
        }

        $classes = $this->teacherProfileInterface
            ->getTeacherClassesForYear($user->id, $year, $gradeId)
            ->map(function ($profile) {
                if (! $profile->class) {
                    return null;
                }

                return [
                    'id'        => $profile->class->id,
                    'className' => $profile->class->className,
                ];
            })
            ->filter()
            ->unique('id')
            ->values();

        return response()->json($classes, 200);
    }

    public function getTeacherGrades(string $year): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (! $year) {
            return response()->json(['message' => 'Academic year is required'], 400);
        }

        $userType = $user->employeeType;

        if ($userType === 'Student' || $userType === 'Parent' || $userType === null) {
            return response()->json(['message' => 'Teachers Only Can View Grades'], 401);
        }

        $grades = $this->teacherProfileInterface
            ->getTeacherGradesForYear($user->id, $year)
            ->map(function ($profile) {
                if (! $profile->grade) {
                    return null;
                }

                return [
                    'id'    => $profile->grade->id,
                    'grade' => $profile->grade->grade,
                ];
            })
            ->filter()
            ->unique('id')
            ->values();

        return response()->json($grades, 200);
    }

    public function getTeacherMediums(string $year): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (! $year) {
            return response()->json(['message' => 'Academic year is required'], 400);
        }

        $userType = $user->employeeType;

        if ($userType === 'Student' || $userType === 'Parent' || $userType === null) {
            return response()->json(['message' => 'Teachers Only Can View Mediums'], 401);
        }

        $mediums = $this->teacherProfileInterface
            ->getTeacherMediumsForYear($user->id, $year)
            ->map(fn($profile) => $profile->academicMedium)
            ->filter()
            ->unique()
            ->values();

        return response()->json($mediums, 200);
    }

    public function getTeacherSubject(
        string $year,
        string $gradeId,
        string $classId,
        string $medium
    ): JsonResponse {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (! $year || ! $gradeId || ! $classId || ! $medium) {
            return response()->json(['message' => 'Year, grade, class, and medium are required'], 400);
        }

        $userType = $user->employeeType;

        if ($userType === 'Student' || $userType === 'Parent' || $userType === null) {
            return response()->json(['message' => 'Teachers Only Can View Subjects'], 401);
        }

        $subjects = $this->teacherProfileInterface
            ->getTeacherSubjects($user->id, $year, $gradeId, $classId, $medium)
            ->map(function ($profile) {
                if (! $profile->subject) {
                    return null;
                }

                return [
                    'id'          => $profile->subject->id,
                    'subjectCode' => $profile->subject->subjectCode,
                    'subjectName' => $profile->subject->subjectName,
                ];
            })
            ->filter()
            ->unique('id')
            ->values();

        return response()->json($subjects, 200);
    }

    public function updateByAdmin(ComTeacherProfileRequest $request, int $id): JsonResponse
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
        $data = $request->validated();
        $data['createdByUser'] = $id;
        $data['teacherId'] = $id;


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
}
