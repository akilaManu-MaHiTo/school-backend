<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherAcademicWorks\TeacherAcademicWorksRequest;
use App\Models\ComStudentProfile;
use App\Models\ComTeacherProfile;
use App\Models\TeacherAcademicWorks;
use App\Repositories\All\TeacherAcademicWorks\TeacherAcademicWorksInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class TeacherAcademicWorksController extends Controller
{
    protected TeacherAcademicWorksInterface $teacherAcademicWorksInterface;

    public function __construct(TeacherAcademicWorksInterface $teacherAcademicWorksInterface)
    {
        $this->teacherAcademicWorksInterface = $teacherAcademicWorksInterface;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $works = $this->teacherAcademicWorksInterface->all(
            ['*'],
            ['teacher', 'subject', 'createdByUser'],
            'created_at',
            'desc'
        );

        return response()->json($works, 200);
    }

    public function getTeacherWorksByDate(string $date): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        if (trim($date) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Date is required.',
            ], 422);
        }

        try {
            $normalizedDate = Carbon::parse($date, 'UTC')->toDateString();
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid date format.',
            ], 422);
        }

        $works = TeacherAcademicWorks::with(['teacher', 'subject', 'createdByUser'])
            ->where('createdBy', $user->id)
            ->where(function ($query) use ($normalizedDate) {
                $query->where('date', $normalizedDate)
                    ->orWhere('date', 'like', $normalizedDate . '%')
                    ->orWhereRaw('LEFT(`date`, 10) = ?', [$normalizedDate]);
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json($works, 200);
    }

    public function myWorksByDate(int $id, string $date): JsonResponse
    {
        if ($id <= 0 || trim($date) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Teacher ID and date are required.',
            ], 422);
        }

        try {
            $normalizedDate = Carbon::parse($date, 'UTC')->toDateString();
        } catch (\Exception $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid date format.',
            ], 422);
        }

        $teacherProfile = ComTeacherProfile::query()
            ->with(['grade', 'class'])
            ->where('teacherId', $id)
            ->orderByDesc('academicYear')
            ->orderByDesc('id')
            ->first();

        $works = TeacherAcademicWorks::with(['teacher', 'subject', 'createdByUser'])
            ->where('teacherId', $id)
            ->where(function ($query) use ($normalizedDate) {
                $query->where('date', $normalizedDate)
                    ->orWhere('date', 'like', $normalizedDate . '%')
                    ->orWhereRaw('LEFT(`date`, 10) = ?', [$normalizedDate]);
            })
            ->orderByDesc('created_at')
            ->get();

        $works = $works->map(function (TeacherAcademicWorks $work) use ($teacherProfile) {
            return [
                'id' => $work->id,
                'teacherId' => $work->teacherId,
                'subjectId' => $work->subjectId,
                'title' => $work->title,
                'academicWork' => $work->academicWork,
                'date' => $work->date,
                'time' => $work->time,
                'approved' => $work->approved,
                'createdBy' => $work->createdBy,
                'created_at' => $work->created_at,
                'updated_at' => $work->updated_at,
                'teacher' => $work->teacher,
                'subject' => $work->subject,
                'createdByUser' => $work->createdByUser,
                'teacherGrade' => $teacherProfile?->grade,
                'teacherClass' => $teacherProfile?->class,
                'teacherProfile' => $teacherProfile,
            ];
        });

        return response()->json($works, 200);
    }

    public function getTeacherWorksByAdmin(string $year, int $gradeId, int $classId, string $date, string $clientDate): JsonResponse
    {
        if (trim($year) === '' || $gradeId <= 0 || $classId <= 0 || trim($date) === '' || trim($clientDate) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Year, grade, class, date, and client date are required.',
            ], 422);
        }

        $normalizedFilter = strtolower(trim($date));
        $isMonthly = $normalizedFilter === 'monthly';
        $isWeekFilter = preg_match('/^week\s*([1-4])$/i', $normalizedFilter, $weekMatches) === 1;
        $normalizedDate = null;
        $weekStart = null;
        $weekEnd = null;

        if ($isWeekFilter) {
            try {
                $clientDateCarbon = Carbon::parse($clientDate, 'UTC');
            } catch (\Exception $exception) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid client date format.',
                ], 422);
            }

            $weekNumber = (int) $weekMatches[1];
            $weekStart = $clientDateCarbon->copy()->startOfMonth()->addDays(($weekNumber - 1) * 7)->startOfDay();
            $weekEnd = $weekNumber === 4
                ? $clientDateCarbon->copy()->endOfMonth()->endOfDay()
                : $weekStart->copy()->addDays(6)->endOfDay();
        } elseif (! $isMonthly) {
            try {
                $normalizedDate = Carbon::parse($date, 'UTC')->toDateString();
            } catch (\Exception $exception) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date format.',
                ], 422);
            }
        }

        $studentIds = ComStudentProfile::query()
            ->where('academicYear', $year)
            ->where('academicGradeId', $gradeId)
            ->where('academicClassId', $classId)
            ->pluck('studentId')
            ->unique()
            ->values();

        if ($studentIds->isEmpty()) {
            return response()->json([], 200);
        }

        $query = TeacherAcademicWorks::with(['teacher', 'subject', 'createdByUser'])
            ->whereIn('createdBy', $studentIds)
            ->orderByDesc('created_at');

        if ($isWeekFilter) {
            $query->whereRaw('LEFT(`date`, 10) BETWEEN ? AND ?', [
                $weekStart?->toDateString(),
                $weekEnd?->toDateString(),
            ]);

            $works = $query->get();

            $groupedWorks = $works
                ->groupBy(function (TeacherAcademicWorks $work) {
                    return $this->normalizeWorkDate($work->date);
                })
                ->map(function ($dailyWorks, string $workDate) {
                    return [
                        'date' => $workDate,
                        'works' => $dailyWorks->values(),
                    ];
                })
                ->sortBy('date')
                ->values();

            return response()->json($groupedWorks, 200);
        }

        if (! $isMonthly) {
            $query->where(function ($query) use ($normalizedDate) {
                $query->where('date', $normalizedDate)
                    ->orWhere('date', 'like', $normalizedDate . '%')
                    ->orWhereRaw('LEFT(`date`, 10) = ?', [$normalizedDate]);
            });

            $works = $query->get();

            return response()->json($works, 200);
        }

        $works = $query->get();

        $groupedWorks = $works
            ->groupBy(function (TeacherAcademicWorks $work) {
                return $this->normalizeWorkDate($work->date);
            })
            ->map(function ($dailyWorks, string $workDate) {
                return [
                    'date' => $workDate,
                    'works' => $dailyWorks->values(),
                ];
            })
            ->sortBy('date')
            ->values();

        return response()->json($groupedWorks, 200);
    }

    public function getTeacherByStudentIdAndSubjectId(int $subjectId, int $studentId, string $year): JsonResponse
    {
        if ($subjectId <= 0 || $studentId <= 0 || trim($year) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Subject ID, student ID and academic year are required.',
            ], 422);
        }

        $studentProfile = ComStudentProfile::query()
            ->where('studentId', $studentId)
            ->where('academicYear', $year)
            ->first();

        if (! $studentProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found for the given year.',
            ], 404);
        }

        $teacherProfiles = ComTeacherProfile::query()
            ->with(['teacher', 'grade', 'subject', 'class'])
            ->where('academicYear', $year)
            ->where('academicGradeId', $studentProfile->academicGradeId)
            ->where('academicClassId', $studentProfile->academicClassId)
            ->where('academicMedium', $studentProfile->academicMedium)
            ->where('academicSubjectId', $subjectId)
            ->get();

        if ($teacherProfiles->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found for the given student, subject and year.',
            ], 404);
        }

        $teachers = $teacherProfiles
            ->pluck('teacher')
            ->filter()
            ->unique('id')
            ->values()
            ->map(function ($teacher) {
                return [
                    'id'               => $teacher->id,
                    'name'             => $teacher->name,
                    'userName'         => $teacher->userName,
                    'nameWithInitials' => $teacher->nameWithInitials,
                    'email'            => $teacher->email,
                    'employeeType'     => $teacher->employeeType,
                    'employeeNumber'   => $teacher->employeeNumber,
                ];
            });

        if ($teachers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher user record not found.',
            ], 404);
        }

        return response()->json($teachers, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TeacherAcademicWorksRequest $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $payload = $request->validated();
        $payload['createdBy'] = $user->id;

        $work = $this->teacherAcademicWorksInterface->create($payload);

        return response()->json([
            'success' => true,
            'message' => 'Teacher academic work created successfully.',
            'data' => $work ? $work->load(['teacher', 'subject', 'createdByUser']) : null,
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TeacherAcademicWorksRequest $request, int $id): JsonResponse
    {
        $work = TeacherAcademicWorks::query()->whereKey($id)->first();
        if (! $work) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher academic work not found.',
            ], 404);
        }

        $payload = $request->validated();
        $payload['createdBy'] = Auth::id();

        $this->teacherAcademicWorksInterface->update($id, $payload);
        $work->refresh()->load(['teacher', 'subject', 'createdByUser']);

        return response()->json([
            'success' => true,
            'message' => 'Teacher academic work updated successfully.',
            'data' => $work,
        ], 200);
    }

    /**
     * Approve the specified academic work record.
     */
    public function approveTeacherRecord(int $id): JsonResponse
    {
        $work = TeacherAcademicWorks::query()->whereKey($id)->first();

        if (! $work) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher academic work not found.',
            ], 404);
        }

        $work->approved = true;
        $work->save();

        $work->load(['teacher', 'subject', 'createdByUser']);

        return response()->json([
            'success' => true,
            'message' => 'Teacher academic work approved successfully.',
            'data' => $work,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $work = TeacherAcademicWorks::query()->whereKey($id)->first();
        if (! $work) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher academic work not found.',
            ], 404);
        }

        TeacherAcademicWorks::query()->whereKey($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Teacher academic work deleted successfully.',
        ], 200);
    }

    private function normalizeWorkDate(string $value): string
    {
        try {
            return Carbon::parse($value, 'UTC')->toDateString();
        } catch (\Exception $exception) {
            return substr($value, 0, 10);
        }
    }
}
