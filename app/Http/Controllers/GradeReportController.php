<?php

namespace App\Http\Controllers;

use App\Models\StudentMarks;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class GradeReportController extends Controller
{
    public function getGradeReportBarChart(string $year, int $grade, string $examType): JsonResponse
    {
        $rows = StudentMarks::query()
            ->select([
                'com_class_mngs.className',
                'com_subjects.subjectName',
                'com_subjects.colorCode',
                'student_marks.academicSubjectId',
                DB::raw('AVG(CAST(student_marks.studentMark AS DECIMAL(10,2))) as averageMark'),
                DB::raw('COUNT(student_marks.id) as studentCount'),
            ])
            ->join('com_student_profiles', 'student_marks.studentProfileId', '=', 'com_student_profiles.id')
            ->join('com_class_mngs', 'com_student_profiles.academicClassId', '=', 'com_class_mngs.id')
            ->join('com_grades', 'com_student_profiles.academicGradeId', '=', 'com_grades.id')
            ->join('com_subjects', 'student_marks.academicSubjectId', '=', 'com_subjects.id')
            ->where('student_marks.academicYear', $year)
            ->where('student_marks.academicTerm', $examType)
            ->where('com_student_profiles.academicGradeId', $grade)
            ->where('student_marks.isAbsentStudent', false)
            ->whereNotNull('student_marks.studentMark')
            ->groupBy([
                'com_class_mngs.className',
                'com_subjects.subjectName',
                'com_subjects.colorCode',
                'student_marks.academicSubjectId',
            ])
            ->orderBy('com_class_mngs.className')
            ->orderBy('com_subjects.subjectName')
            ->get();

        $grouped = [];

        foreach ($rows as $row) {
            $className = $row->className;

            if (! isset($grouped[$className])) {
                $grouped[$className] = [
                    'className' => $className,
                    'subjects'  => [],
                ];
            }

            $grouped[$className]['subjects'][] = [
                'subjectId'    => (int) $row->academicSubjectId,
                'subjectName'  => $row->subjectName,
                'colorCode'    => $row->colorCode,
                'averageMark'  => (float) $row->averageMark,
                'studentCount' => (int) $row->studentCount,
            ];
        }

        foreach ($grouped as &$classGroup) {
            usort($classGroup['subjects'], function (array $a, array $b) {
                return strcmp($a['subjectName'], $b['subjectName']);
            });
        }
        unset($classGroup);

        $data = array_values($grouped);

        return response()->json(
            $data,
            status: 200
        );
    }

    public function getGradeReportGradeMArkCountBarChart(string $year, int $grade, string $examType, string $gradeMark): JsonResponse
    {
        $gradeMark = strtoupper($gradeMark);

        $rows = StudentMarks::query()
            ->join('com_student_profiles', 'student_marks.studentProfileId', '=', 'com_student_profiles.id')
            ->join('com_class_mngs', 'com_student_profiles.academicClassId', '=', 'com_class_mngs.id')
            ->join('com_subjects', 'student_marks.academicSubjectId', '=', 'com_subjects.id')
            ->where('student_marks.academicYear', $year)
            ->where('student_marks.academicTerm', $examType)
            ->where('com_student_profiles.academicGradeId', $grade)
            ->where('student_marks.isAbsentStudent', false)
            ->whereNotNull('student_marks.studentMark')
            ->whereNotNull('student_marks.markGrade')
            ->where('student_marks.markGrade', $gradeMark)
            ->selectRaw('com_class_mngs.className as className, student_marks.academicSubjectId as subjectId, com_subjects.subjectName as subjectName, com_subjects.colorCode as colorCode, COUNT(*) as gradeCount')
            ->groupBy('com_class_mngs.className', 'student_marks.academicSubjectId', 'com_subjects.subjectName', 'com_subjects.colorCode')
            ->get();

        $grouped = [];

        foreach ($rows as $row) {
            $className = $row->className;

            if (! isset($grouped[$className])) {
                $grouped[$className] = [
                    'className' => $className,
                    'subjects'  => [],
                ];
            }

            $grouped[$className]['subjects'][] = [
                'subjectId'   => (int) $row->subjectId,
                'subjectName' => $row->subjectName,
                'colorCode'   => $row->colorCode,
                'count'       => (int) $row->gradeCount,
            ];
        }

        foreach ($grouped as &$classGroup) {
            usort($classGroup['subjects'], function (array $a, array $b) {
                return strcmp($a['subjectName'], $b['subjectName']);
            });
        }
        unset($classGroup);

        $data = array_values($grouped);

        return response()->json(
            $data,
            status: 200
        );
    }
}
