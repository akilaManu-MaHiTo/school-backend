<?php

namespace App\Http\Controllers;

use App\Models\ComClassMng;
use App\Models\ComGrades;
use App\Models\ComStudentProfile;
use App\Models\StudentMarks;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class ClassReportController extends Controller
{
    public function getClassBarChart(string $year, int $gradeId, int $classId, string $examType): JsonResponse
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

    /**
     * @param string $year
     * @param int    $gradeId
     * @param int    $classId
     * @param string $examType
     */
    public function getClassReportCard(string $year, int $gradeId, int $classId, string $examType): JsonResponse
    {
        $studentProfiles = $this->getStudentProfiles($year, $gradeId, $classId);

        if ($studentProfiles->isEmpty()) {
            return response()->json([
                'success' => true,
                'data'    => null,
            ]);
        }

        $marks = $this->getMarksForProfiles($studentProfiles, $year, $examType);

        $gradeModel = ComGrades::find($gradeId);
        $classModel = ComClassMng::find($classId);

        $studentCount = $studentProfiles->count();
        $subjects     = $this->buildSubjectsPayload($marks);
        $markData     = $this->buildMarkDataPayload($studentProfiles, $marks, $studentCount);
        $markData     = $this->assignPositions($markData);

        $data = [
            'className'    => $classModel?->className,
            'grade'        => $gradeModel?->grade,
            'studentCount' => $studentCount,
            'subjects'     => $subjects,
            'MarkData'     => $markData,
        ];

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * @return Collection<int, ComStudentProfile>
     */
    private function getStudentProfiles(string $year, int $gradeId, int $classId): Collection
    {
        return ComStudentProfile::query()
            ->with(['student', 'grade', 'class'])
            ->where('academicGradeId', $gradeId)
            ->where('academicClassId', $classId)
            ->where('academicYear', $year)
            ->get();
    }

    /**
     * @param Collection<int, ComStudentProfile> $studentProfiles
     * @return Collection<int, StudentMarks>
     */
    private function getMarksForProfiles(Collection $studentProfiles, string $year, string $examType): Collection
    {
        $studentProfileIds = $studentProfiles->pluck('id');

        return StudentMarks::query()
            ->with(['studentProfile.student', 'subject'])
            ->whereIn('studentProfileId', $studentProfileIds)
            ->where('academicYear', $year)
            ->where('academicTerm', $examType)
            ->get();
    }

    /**
     * @param Collection<int, StudentMarks> $marks
     * @return array<int, array<string, mixed>>
     */
    private function buildSubjectsPayload(Collection $marks): array
    {
        return $marks
            ->pluck('subject')
            ->filter()
            ->unique('id')
            ->map(function ($subject) {
                return [
                    'id'              => $subject->id,
                    'subjectCode'     => $subject->subjectCode,
                    'subjectName'     => $subject->subjectName,
                    'isBasketSubject' => (bool) ($subject->isBasketSubject ?? false),
                    'group'           => $subject->basketGroup,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param Collection<int, ComStudentProfile> $studentProfiles
     * @param Collection<int, StudentMarks>      $marks
     * @return array<int, array<string, mixed>>
     */
    private function buildMarkDataPayload(Collection $studentProfiles, Collection $marks, int $studentCount): array
    {
        $marksByStudent = $marks->groupBy('studentProfileId');

        $markData = [];

        foreach ($studentProfiles as $profile) {
            $student      = $profile->student;
            $studentMarks = $marksByStudent->get($profile->id, collect());

            $marksObject = [];
            $totalMarks  = 0.0;
            $marksCount  = 0;

            foreach ($studentMarks as $mark) {
                $subject = $mark->subject;
                if (! $subject) {
                    continue;
                }

                $numericMark = is_null($mark->studentMark) ? null : (float) $mark->studentMark;

                if ($numericMark !== null && ! $mark->isAbsentStudent) {
                    $totalMarks += $numericMark;
                    $marksCount++;
                }

                $subjectKey                = $subject->subjectName;
                $marksObject[$subjectKey] = [
                    'marks'   => $numericMark,
                    'subject' => $subject->subjectName,
                ];

                if ($subject->isBasketSubject ?? false) {
                    $groupKey = $subject->basketGroup;
                    if ($groupKey) {
                        $marksObject[$groupKey] = [
                            'marks'   => $numericMark,
                            'subject' => $subject->subjectName,
                        ];
                    }
                }
            }

            $average = $marksCount > 0 ? $totalMarks / $studentCount : 0.0;

            $markData[] = [
                'userName'         => $student?->userName,
                'email'            => $student?->email,
                'nameWithInitials' => $student?->name,
                'marks'            => [$marksObject],
                'averageOfMarks'   => $average,
                'position'         => null,
            ];
        }

        return $markData;
    }

    /**
     * @param array<int, array<string, mixed>> $markData
     * @return array<int, array<string, mixed>>
     */
    private function assignPositions(array $markData): array
    {
        usort($markData, function (array $a, array $b) {
            return $b['averageOfMarks'] <=> $a['averageOfMarks'];
        });

        $lastAverage  = null;
        $lastPosition = 0;

        foreach ($markData as $index => &$entry) {
            $currentAverage = $entry['averageOfMarks'];
            if ($lastAverage === null || $currentAverage < $lastAverage) {
                $lastPosition = $index + 1;
                $lastAverage  = $currentAverage;
            }
            $entry['position'] = $lastPosition;
        }
        unset($entry);

        return $markData;
    }
}
