<?php

namespace App\Http\Controllers;

use App\Models\ComStudentProfile;
use App\Models\ComTeacherProfile;
use App\Models\StudentMarks;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarkCheckingReportController extends Controller
{
    public function checkMarkTeacher(string $year, int $grade, string $examType, Request $request): JsonResponse
    {
        // Optional teacher search (username, email, full name, employeeId)
        $search = trim((string) $request->query('search', ''));

        // Get all teacher profile assignments with related info, filtered by year and grade
        $teacherQuery = ComTeacherProfile::query()
            ->with(['teacher', 'grade', 'class', 'subject'])
            ->where('academicYear', $year)
            ->where('academicGradeId', $grade)
            ->whereHas('teacher', function ($q) {
                $q->where('employeeType', 'Teacher');
            });

        if ($search !== '') {
            $teacherQuery->whereHas('teacher', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('userName', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employeeNumber', 'like', "%{$search}%");
            });
        }

        $teacherProfiles = $teacherQuery->get();

        if ($teacherProfiles->isEmpty()) {
            return response()->json([]);
        }

        // Determine which exam terms to evaluate
        $validTerms = ['Term 1', 'Term 2', 'Term 3'];
        if (strcasecmp($examType, 'All') === 0) {
            $termsToProcess = $validTerms;
        } else {
            $termsToProcess = [$examType];
        }

        $results = [];

        foreach ($teacherProfiles as $profile) {
            if (! $profile->teacher || ! $profile->grade || ! $profile->class || ! $profile->subject) {
                continue;
            }

            $year   = $profile->academicYear;
            $gradeId = $profile->academicGradeId;
            $classId = $profile->academicClassId;
            $medium  = $profile->academicMedium;
            $subjectId = $profile->academicSubjectId;
            $teacherId = $profile->teacherId;

            // Find all students in this class/year/medium
            $studentProfiles = ComStudentProfile::query()
                ->where('academicGradeId', $gradeId)
                ->where('academicClassId', $classId)
                ->where('academicYear', $year)
                ->where('academicMedium', $medium)
                ->get();

            if ($studentProfiles->isEmpty()) {
                foreach ($termsToProcess as $termLabel) {
                    $this->pushTeacherResult(
                        $results,
                        $teacherId,
                        $profile->teacher->name,
                        $profile->teacher->email,
                        $termLabel,
                        [
                            'academicYear'            => $year,
                            'academicMedium'          => $medium,
                            'gradeId'                 => $profile->grade->id,
                            'gradeName'               => $profile->grade->grade,
                            'classId'                 => $profile->class->id,
                            'className'               => $profile->class->className,
                            'subjectId'               => $profile->subject->id,
                            'subjectCode'             => $profile->subject->subjectCode,
                            'subjectName'             => $profile->subject->subjectName,
                            'totalStudentsForSubject' => 0,
                            'markedStudentsCount'     => 0,
                            'pendingStudentsCount'    => 0,
                        ]
                    );
                }

                continue;
            }

            // If this subject is a basket/optional subject, we only consider
            // students who have that subject in their basketSubjectsIds array.
            $basketSubjectProfiles = $studentProfiles->filter(function (ComStudentProfile $studentProfile) use ($subjectId) {
                $basketSubjectArray = array_map('intval', $studentProfile->basketSubjectsIds ?? []);

                return in_array($subjectId, $basketSubjectArray, true);
            });

            $profilesForSubject = $basketSubjectProfiles->isNotEmpty()
                ? $basketSubjectProfiles
                : $studentProfiles;

            $expectedCount = $profilesForSubject->count();

            if ($expectedCount === 0) {
                foreach ($termsToProcess as $termLabel) {
                    $this->pushTeacherResult(
                        $results,
                        $teacherId,
                        $profile->teacher->name,
                        $profile->teacher->email,
                        $termLabel,
                        [
                            'academicYear'            => $year,
                            'academicMedium'          => $medium,
                            'gradeId'                 => $profile->grade->id,
                            'gradeName'               => $profile->grade->grade,
                            'classId'                 => $profile->class->id,
                            'className'               => $profile->class->className,
                            'subjectId'               => $profile->subject->id,
                            'subjectCode'             => $profile->subject->subjectCode,
                            'subjectName'             => $profile->subject->subjectName,
                            'totalStudentsForSubject' => 0,
                            'markedStudentsCount'     => 0,
                            'pendingStudentsCount'    => 0,
                        ]
                    );
                }

                continue;
            }

            $studentProfileIds = $profilesForSubject->pluck('id');

            foreach ($termsToProcess as $termLabel) {
                // Build query for marks by this teacher for this subject/year and term
                $marksQuery = StudentMarks::query()
                    ->whereIn('studentProfileId', $studentProfileIds)
                    ->where('academicSubjectId', $subjectId)
                    ->where('academicYear', $year)
                    ->where('createdByTeacher', $teacherId)
                    ->where('academicTerm', $termLabel);

                // Count how many unique students this teacher has marked for this term
                $markedStudentsCount = $marksQuery
                    ->distinct('studentProfileId')
                    ->count('studentProfileId');

                $this->pushTeacherResult(
                    $results,
                    $teacherId,
                    $profile->teacher->name,
                    $profile->teacher->email,
                    $termLabel,
                    [
                        'academicYear'            => $year,
                        'academicMedium'          => $medium,
                        'gradeId'                 => $profile->grade->id,
                        'gradeName'               => $profile->grade->grade,
                        'classId'                 => $profile->class->id,
                        'className'               => $profile->class->className,
                        'subjectId'               => $profile->subject->id,
                        'subjectCode'             => $profile->subject->subjectCode,
                        'subjectName'             => $profile->subject->subjectName,
                        'totalStudentsForSubject' => $expectedCount,
                        'markedStudentsCount'     => $markedStudentsCount,
                        'pendingStudentsCount'    => max($expectedCount - $markedStudentsCount, 0),
                    ]
                );
            }
        }

        return response()->json($results, 200);
    }

    /**
     * Group results by teacher and term, and append class/subject mark-check entry.
     */
    private function pushTeacherResult(array &$results, int $teacherId, string $teacherName, string $teacherEmail, string $termLabel, array $markCheckingRow): void
    {
        foreach ($results as &$teacherRow) {
            if (($teacherRow['teacherId'] ?? null) === $teacherId) {
                if (! isset($teacherRow['markChecking'][$termLabel])) {
                    $teacherRow['markChecking'][$termLabel] = [];
                }
                $teacherRow['markChecking'][$termLabel][] = $markCheckingRow;

                return;
            }
        }

        $results[] = [
            'teacherId'    => $teacherId,
            'teacherName'  => $teacherName,
            'teacherEmail' => $teacherEmail,
            'markChecking' => [
                $termLabel => [$markCheckingRow],
            ],
        ];
    }
}
