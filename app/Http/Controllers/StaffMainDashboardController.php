<?php

namespace App\Http\Controllers;

use App\Models\ComStudentProfile;
use App\Models\ComSubjects;
use App\Models\ComTeacherProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class StaffMainDashboardController extends Controller
{
    /**
     * Get student counts per grade and class for a given academic year.
     */
    public function getYearClassesStudentCount(string $year): JsonResponse
    {
        $profiles = ComStudentProfile::query()
            ->with(['grade', 'class', 'student'])
            ->where('academicYear', $year)
            ->whereHas('student', function ($query) {
                $query->where('availability', 1);
            })
            ->get();

        if ($profiles->isEmpty()) {
            return response()->json([]);
        }

        $result = $profiles
            ->groupBy('academicGradeId')
            ->map(function ($gradeProfiles) {
                $grade = optional($gradeProfiles->first()->grade);

                $classes = $gradeProfiles
                    ->groupBy('academicClassId')
                    ->map(function ($classProfiles) {
                        $class = optional($classProfiles->first()->class);

                        $basketCounts = [];

                        foreach ($classProfiles as $profile) {
                            $subjectIds = is_array($profile->basketSubjectsIds)
                                ? $profile->basketSubjectsIds
                                : (is_string($profile->basketSubjectsIds)
                                    ? json_decode($profile->basketSubjectsIds, true) ?? []
                                    : []);

                            foreach ($subjectIds as $subjectId) {
                                $id = (int) $subjectId;
                                if ($id <= 0) {
                                    continue;
                                }
                                $basketCounts[$id] = ($basketCounts[$id] ?? 0) + 1;
                            }
                        }

                        $subjectDetails = [];
                        if (! empty($basketCounts)) {
                            $subjects = ComSubjects::query()
                                ->whereIn('id', array_keys($basketCounts))
                                ->get()
                                ->keyBy('id');

                            foreach ($basketCounts as $subjectId => $count) {
                                $subject = $subjects->get($subjectId);
                                if (! $subject) {
                                    continue;
                                }

                                $subjectDetails[] = [
                                    'subjectId'   => $subject->id,
                                    'subjectCode' => $subject->subjectCode,
                                    'subjectName' => $subject->subjectName,
                                    'colorCode'   => $subject->colorCode,
                                    'studentCount'=> $count,
                                ];
                            }
                        }

                        return [
                            'classId'      => $class?->id,
                            'className'    => $class?->className,
                            'studentCount' => $classProfiles->count(),
                            'basketSubjects' => $subjectDetails,
                        ];
                    })
                    ->values();

                return [
                    'gradeId'       => $grade?->id,
                    'grade'         => $grade?->grade,
                    'totalStudents' => $gradeProfiles->count(),
                    'classes'       => $classes,
                ];
            })
            ->values();

        return response()->json($result, 200);
    }

    public function getAllStudentsCount(string $year): JsonResponse
    {
        $count = ComStudentProfile::query()
            ->where('academicYear', $year)
            ->whereHas('student', function ($query) {
                $query->where('availability', 1)
                    ->where('employeeType', 'Student');
            })
            ->distinct('studentId')
            ->count('studentId');

        return response()->json([
            'type'  => 'Student',
            'year'  => $year,
            'count' => $count,
        ], 200);
    }

    public function getAllTeachersCount(string $year): JsonResponse
    {
        $count = ComTeacherProfile::query()
            ->where('academicYear', $year)
            ->whereHas('teacher', function ($query) {
                $query->where('availability', 1)
                    ->where('employeeType', 'Teacher');
            })
            ->distinct('teacherId')
            ->count('teacherId');

        return response()->json([
            'type'  => 'Teacher',
            'year'  => $year,
            'count' => $count,
        ], 200);
    }

    public function getAllParentsCount(): JsonResponse
    {
        $count = User::query()
            ->where('employeeType', 'Parent')
            ->where('availability', 1)
            ->count();

        return response()->json([
            'type'  => 'Parent',
            'count' => $count,
        ], 200);
    }
}
