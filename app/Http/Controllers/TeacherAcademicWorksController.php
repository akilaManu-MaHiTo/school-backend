<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherAcademicWorks\TeacherAcademicWorksRequest;
use App\Models\ComStudentProfile;
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
            ->where(function ($query) use ($normalizedDate) {
                $query->where('date', $normalizedDate)
                    ->orWhere('date', 'like', $normalizedDate . '%')
                    ->orWhereRaw('LEFT(`date`, 10) = ?', [$normalizedDate]);
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json($works, 200);
    }

    public function getTeacherWorksByAdmin(string $year, int $gradeId, int $classId, string $date): JsonResponse
    {
        if (trim($year) === '' || $gradeId <= 0 || $classId <= 0 || trim($date) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Year, grade, class, and date are required.',
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

        $works = TeacherAcademicWorks::with(['teacher', 'subject', 'createdByUser'])
            ->whereIn('createdBy', $studentIds)
            ->where(function ($query) use ($normalizedDate) {
                $query->where('date', $normalizedDate)
                    ->orWhere('date', 'like', $normalizedDate . '%')
                    ->orWhereRaw('LEFT(`date`, 10) = ?', [$normalizedDate]);
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json($works, 200);
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
        $work = TeacherAcademicWorks::find($id);
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
        $work = TeacherAcademicWorks::find($id);

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
        $work = TeacherAcademicWorks::find($id);
        if (! $work) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher academic work not found.',
            ], 404);
        }

        $work->delete();

        return response()->json([
            'success' => true,
            'message' => 'Teacher academic work deleted successfully.',
        ], 200);
    }
}
