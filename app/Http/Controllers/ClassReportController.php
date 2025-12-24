<?php

namespace App\Http\Controllers;

use App\Models\ComStudentProfile;
use App\Models\StudentMarks;
use Illuminate\Http\JsonResponse;

class ClassReportController extends Controller
{
    public function getClassReport(string $year, int $gradeId, int $classId, string $examType): JsonResponse
    {
        // Find all student profiles for this class and academic year
        $studentProfileIds = ComStudentProfile::query()
            ->where('academicGradeId', $gradeId)
            ->where('academicClassId', $classId)
            ->where('academicYear', $year)
            ->pluck('id');

        if ($studentProfileIds->isEmpty()) {
            return response()->json([
                'success' => true,
                'data'    => [],
            ]);
        }

        // Aggregate marks per subject for the given term, excluding absent students
        $rows = StudentMarks::query()
            ->join('com_subjects', 'student_marks.academicSubjectId', '=', 'com_subjects.id')
            ->whereIn('student_marks.studentProfileId', $studentProfileIds)
            ->where('student_marks.academicYear', $year)
            ->where('student_marks.academicTerm', $examType)
            ->where('student_marks.isAbsentStudent', false)
            ->whereNotNull('student_marks.studentMark')
            ->selectRaw('student_marks.academicSubjectId as subjectId, com_subjects.subjectName as subjectName, SUM(student_marks.studentMark) as totalMarks, COUNT(*) as studentCount')
            ->groupBy('student_marks.academicSubjectId', 'com_subjects.subjectName')
            ->get();

        $data = $rows->map(function ($row) {
            $totalMarks = (float) $row->totalMarks;
            $studentCount = (int) $row->studentCount;

            return [
                'subjectName'  => $row->subjectName,
                'totalMarks'   => $totalMarks,
                'average'      => $studentCount > 0 ? $totalMarks / $studentCount : 0.0,
                'studentCount' => $studentCount,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }
}
