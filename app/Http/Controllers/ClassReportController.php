<?php

namespace App\Http\Controllers;

use App\Models\ComClassMng;
use App\Models\ComClassTeacher;
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
            ->selectRaw('student_marks.academicSubjectId as subjectId, com_subjects.subjectName as subjectName, com_subjects.colorCode as colorCode, SUM(student_marks.studentMark) as totalMarks, COUNT(*) as studentCount')
            ->groupBy('student_marks.academicSubjectId', 'com_subjects.subjectName', 'com_subjects.colorCode')
            ->get();

        $data = $rows->map(function ($row) {
            $totalMarks = (float) $row->totalMarks;
            $studentCount = (int) $row->studentCount;

            return [
                'subjectName'  => $row->subjectName,
                'totalMarks'   => $totalMarks,
                'subjectColorCode' => $row->colorCode,
                'average'      => $studentCount > 0 ? $totalMarks / $studentCount : 0.0,
                'studentCount' => $studentCount,
            ];
        })->values();

        return response()->json(
            $data,
        );
    }

    /**
     * Return per-subject counts for a specific mark grade (A, B, C, ...).
     * Defaults to "A" when no grade is provided.
     */
    public function getClassBarChartByMarkGrade(string $year, int $gradeId, int $classId, string $examType, string $markGrade = 'A'): JsonResponse
    {
        // Normalize mark grade to upper-case
        $markGrade = strtoupper($markGrade);

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

        // Get the distinct subjects for this set of student profiles and term
        $subjects = StudentMarks::query()
            ->join('com_subjects', 'student_marks.academicSubjectId', '=', 'com_subjects.id')
            ->whereIn('student_marks.studentProfileId', $studentProfileIds)
            ->where('student_marks.academicYear', $year)
            ->where('student_marks.academicTerm', $examType)
            ->selectRaw('student_marks.academicSubjectId as subjectId, com_subjects.subjectName as subjectName, com_subjects.colorCode as colorCode')
            ->groupBy('student_marks.academicSubjectId', 'com_subjects.subjectName', 'com_subjects.colorCode')
            ->get();

        // Aggregate counts per subject for the given term and mark grade
        $counts = StudentMarks::query()
            ->whereIn('student_marks.studentProfileId', $studentProfileIds)
            ->where('student_marks.academicYear', $year)
            ->where('student_marks.academicTerm', $examType)
            ->where('student_marks.isAbsentStudent', false)
            ->whereNotNull('student_marks.studentMark')
            ->where('student_marks.markGrade', $markGrade)
            ->selectRaw('student_marks.academicSubjectId as subjectId, COUNT(*) as gradeCount')
            ->groupBy('student_marks.academicSubjectId')
            ->pluck('gradeCount', 'subjectId');

        $data = $subjects->map(function ($subject) use ($counts) {
            $subjectId = $subject->subjectId;
            $count = isset($counts[$subjectId]) ? (int) $counts[$subjectId] : 0;

            return [
                'subjectName'       => $subject->subjectName,
                'subjectColorCode'  => $subject->colorCode,
                'count'             => $count,
            ];
        })->values();

        return response()->json(
            $data,
        );
    }

    /**
     * Return per-term, per-subject counts for a specific mark grade (A, B, C, ...).
     * Example response: ['term1' => [...], 'term2' => [...], 'term3' => [...]]
     */
    public function getClassAllBarChartByMarkGrade(string $year, int $gradeId, int $classId, string $markGrade = 'A'): JsonResponse
    {
        $markGrade = strtoupper($markGrade);

        // Find all student profiles for this class and academic year
        $studentProfileIds = ComStudentProfile::query()
            ->where('academicGradeId', $gradeId)
            ->where('academicClassId', $classId)
            ->where('academicYear', $year)
            ->pluck('id');

        if ($studentProfileIds->isEmpty()) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'term1' => [],
                    'term2' => [],
                    'term3' => [],
                ],
            ]);
        }

        $terms = [
            'Term 1' => 'term1',
            'Term 2' => 'term2',
            'Term 3' => 'term3',
        ];

        $result = [];

        foreach ($terms as $termLabel => $key) {
            // Get distinct subjects for this term
            $subjects = StudentMarks::query()
                ->join('com_subjects', 'student_marks.academicSubjectId', '=', 'com_subjects.id')
                ->whereIn('student_marks.studentProfileId', $studentProfileIds)
                ->where('student_marks.academicYear', $year)
                ->where('student_marks.academicTerm', $termLabel)
                ->selectRaw('student_marks.academicSubjectId as subjectId, com_subjects.subjectName as subjectName, com_subjects.colorCode as colorCode')
                ->groupBy('student_marks.academicSubjectId', 'com_subjects.subjectName', 'com_subjects.colorCode')
                ->get();

            if ($subjects->isEmpty()) {
                $result[$key] = [];
                continue;
            }

            // Get counts for provided mark grade per subject for this term
            $counts = StudentMarks::query()
                ->whereIn('student_marks.studentProfileId', $studentProfileIds)
                ->where('student_marks.academicYear', $year)
                ->where('student_marks.academicTerm', $termLabel)
                ->where('student_marks.isAbsentStudent', false)
                ->whereNotNull('student_marks.studentMark')
                ->where('student_marks.markGrade', $markGrade)
                ->selectRaw('student_marks.academicSubjectId as subjectId, COUNT(*) as gradeCount')
                ->groupBy('student_marks.academicSubjectId')
                ->pluck('gradeCount', 'subjectId');

            $data = $subjects->map(function ($subject) use ($counts) {
                $subjectId = $subject->subjectId;
                $count = isset($counts[$subjectId]) ? (int) $counts[$subjectId] : 0;

                return [
                    'subjectName'      => $subject->subjectName,
                    'subjectColorCode' => $subject->colorCode,
                    'count'            => $count,
                ];
            })->values();

            $result[$key] = $data;
        }

        return response()->json(
            $result,
        );
    }

    /**
     * Return bar chart data for Term 1, Term 2 and Term 3 separately
     * with total and average calculated for each term.
     */
    public function getClassAllBarChart(string $year, int $gradeId, int $classId): JsonResponse
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
                'data'    => [
                    'term1' => [],
                    'term2' => [],
                    'term3' => [],
                ],
            ]);
        }

        $terms = [
            'Term 1' => 'term1',
            'Term 2' => 'term2',
            'Term 3' => 'term3',
        ];

        $result = [];

        foreach ($terms as $termLabel => $key) {
            $rows = StudentMarks::query()
                ->join('com_subjects', 'student_marks.academicSubjectId', '=', 'com_subjects.id')
                ->whereIn('student_marks.studentProfileId', $studentProfileIds)
                ->where('student_marks.academicYear', $year)
                ->where('student_marks.academicTerm', $termLabel)
                ->where('student_marks.isAbsentStudent', false)
                ->whereNotNull('student_marks.studentMark')
                ->selectRaw('student_marks.academicSubjectId as subjectId, com_subjects.subjectName as subjectName, com_subjects.colorCode as colorCode, SUM(student_marks.studentMark) as totalMarks, COUNT(*) as studentCount')
                ->groupBy('student_marks.academicSubjectId', 'com_subjects.subjectName', 'com_subjects.colorCode')
                ->get();

            $data = $rows->map(function ($row) {
                $totalMarks   = (float) $row->totalMarks;
                $studentCount = (int) $row->studentCount;

                return [
                    'subjectName'       => $row->subjectName,
                    'subjectColorCode'  => $row->colorCode,
                    'totalMarks'        => $totalMarks,
                    'average'           => $studentCount > 0 ? $totalMarks / $studentCount : 0.0,
                    'studentCount'      => $studentCount,
                ];
            })->values();

            $result[$key] = $data;
        }

        return response()->json([
            'success' => true,
            'data'    => $result,
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
     * Return class report cards for all standard terms (Term 1, Term 2, Term 3)
     * in a single response. Each term's dataset follows the same basic format
     * as getClassReportCard.
     */
    public function getClassAllReportCard(string $year, int $gradeId, int $classId): JsonResponse
    {
        $studentProfiles = $this->getStudentProfiles($year, $gradeId, $classId);

        if ($studentProfiles->isEmpty()) {
            return response()->json([
                'success' => true,
                'data'    => [
                    'term1' => null,
                    'term2' => null,
                    'term3' => null,
                ],
            ]);
        }

        $gradeModel   = ComGrades::find($gradeId);
        $classModel   = ComClassMng::find($classId);
        $studentCount = $studentProfiles->count();

        $terms = [
            'Term 1' => 'term1',
            'Term 2' => 'term2',
            'Term 3' => 'term3',
        ];

        $result = [];

        foreach ($terms as $termLabel => $key) {
            $marks = $this->getMarksForProfiles($studentProfiles, $year, $termLabel);

            if ($marks->isEmpty()) {
                $result[$key] = [
                    'term'         => $termLabel,
                    'className'    => $classModel?->className,
                    'grade'        => $gradeModel?->grade,
                    'studentCount' => $studentCount,
                    'subjects'     => [],
                    'MarkData'     => [],
                ];

                continue;
            }

            $subjects = $this->buildSubjectsPayload($marks);
            $markData = $this->buildMarkDataPayload($studentProfiles, $marks, $studentCount);
            $markData = $this->assignPositions($markData);

            $result[$key] = [
                'term'         => $termLabel,
                'className'    => $classModel?->className,
                'grade'        => $gradeModel?->grade,
                'studentCount' => $studentCount,
                'subjects'     => $subjects,
                'MarkData'     => $markData,
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    /**
     * Return table data for a given term showing, per subject,
     * how many students obtained each mark grade (A, B, C, S, F).
     *
     * Example row:
     * [
     *   'subjectId'   => 1,
     *   'subjectName' => 'Mathematics',
     *   'A'           => 10,
     *   'B'           => 5,
     *   'C'           => 3,
     *   'S'           => 1,
     *   'F'           => 0,
     * ]
     */
    public function getMarksGradeTable(string $year, int $gradeId, int $classId, string $examType): JsonResponse
    {
        $studentProfileIds = ComStudentProfile::query()
            ->where('academicGradeId', $gradeId)
            ->where('academicClassId', $classId)
            ->where('academicYear', $year)
            ->pluck('id');

        if ($studentProfileIds->isEmpty()) {
            return response()->json();
        }

        $grades = ['A', 'B', 'C', 'S', 'F'];

        $rows = StudentMarks::query()
            ->join('com_subjects', 'student_marks.academicSubjectId', '=', 'com_subjects.id')
            ->whereIn('student_marks.studentProfileId', $studentProfileIds)
            ->where('student_marks.academicYear', $year)
            ->where('student_marks.academicTerm', $examType)
            ->where('student_marks.isAbsentStudent', false)
            ->whereNotNull('student_marks.studentMark')
            ->whereNotNull('student_marks.markGrade')
            ->selectRaw('student_marks.academicSubjectId as subjectId, com_subjects.subjectName as subjectName, student_marks.markGrade as markGrade, COUNT(*) as gradeCount')
            ->groupBy('student_marks.academicSubjectId', 'com_subjects.subjectName', 'student_marks.markGrade')
            ->get();

        $grouped = $rows->groupBy('subjectId');

        $data = $grouped->map(function (Collection $items) use ($grades) {
            $first = $items->first();

            $gradeCounts = [];
            foreach ($items as $item) {
                $grade                       = strtoupper((string) $item->markGrade);
                $gradeCounts[$grade] = (int) $item->gradeCount;
            }

            $row = [
                'subjectId'   => $first->subjectId,
                'subjectName' => $first->subjectName,
            ];

            foreach ($grades as $grade) {
                $row[$grade] = $gradeCounts[$grade] ?? 0;
            }

            return $row;
        })->values();

        return response()->json(
            $data,
        );
    }

    /**
     * Return table data for all standard terms (Term 1, Term 2, Term 3)
     * showing, per subject and per term, the count of students in each
     * mark grade (A, B, C, S, F).
     *
     * Example response structure:
     * [
     *   'term1' => [ ...subject rows... ],
     *   'term2' => [ ...subject rows... ],
     *   'term3' => [ ...subject rows... ],
     * ]
     */
    public function getAllMarksGradeTable(string $year, int $gradeId, int $classId): JsonResponse
    {
        $studentProfileIds = ComStudentProfile::query()
            ->where('academicGradeId', $gradeId)
            ->where('academicClassId', $classId)
            ->where('academicYear', $year)
            ->pluck('id');

        if ($studentProfileIds->isEmpty()) {
            return response()->json(
                [
                    'term1' => [],
                    'term2' => [],
                    'term3' => [],
                ],
            );
        }

        $grades = ['A', 'B', 'C', 'S', 'F'];

        $terms = [
            'Term 1' => 'term1',
            'Term 2' => 'term2',
            'Term 3' => 'term3',
        ];

        $result = [];

        foreach ($terms as $termLabel => $key) {
            $rows = StudentMarks::query()
                ->join('com_subjects', 'student_marks.academicSubjectId', '=', 'com_subjects.id')
                ->whereIn('student_marks.studentProfileId', $studentProfileIds)
                ->where('student_marks.academicYear', $year)
                ->where('student_marks.academicTerm', $termLabel)
                ->where('student_marks.isAbsentStudent', false)
                ->whereNotNull('student_marks.studentMark')
                ->whereNotNull('student_marks.markGrade')
                ->selectRaw('student_marks.academicSubjectId as subjectId, com_subjects.subjectName as subjectName, student_marks.markGrade as markGrade, COUNT(*) as gradeCount')
                ->groupBy('student_marks.academicSubjectId', 'com_subjects.subjectName', 'student_marks.markGrade')
                ->get();

            if ($rows->isEmpty()) {
                $result[$key] = [];
                continue;
            }

            $grouped = $rows->groupBy('subjectId');

            $termData = $grouped->map(function (Collection $items) use ($grades) {
                $first = $items->first();

                $gradeCounts = [];
                foreach ($items as $item) {
                    $grade                       = strtoupper((string) $item->markGrade);
                    $gradeCounts[$grade] = (int) $item->gradeCount;
                }

                $row = [
                    'subjectId'   => $first->subjectId,
                    'subjectName' => $first->subjectName,
                ];

                foreach ($grades as $grade) {
                    $row[$grade] = $gradeCounts[$grade] ?? 0;
                }

                return $row;
            })->values();

            $result[$key] = $termData;
        }

        return response()->json(
            $result,
        );
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
                $isAbsent    = (bool) $mark->isAbsentStudent;

                if ($numericMark !== null && ! $isAbsent) {
                    $totalMarks += $numericMark;
                    $marksCount++;
                }

                $displayMark = $isAbsent ? 'Ab' : $numericMark;

                $subjectKey                = $subject->subjectName;
                $marksObject[$subjectKey] = [
                    'marks'   => $displayMark,
                    'subject' => $subject->subjectName,
                    'isBasketSubject' => (bool)($subject->isBasketSubject ?? false),
                ];

                if ($subject->isBasketSubject ?? false) {
                    $groupKey = $subject->basketGroup;
                    if ($groupKey) {
                        $marksObject[$groupKey] = [
                            'marks'   => $displayMark,
                            'subject' => $subject->subjectName,
                        ];
                    }
                }
            }

            $average = $marksCount > 0 ? $totalMarks / $studentCount : 0.0;

            $markData[] = [
                'userName'         => $student?->userName,
                'admissionNumber'      => $student->employeeNumber,
                'email'            => $student?->email,
                'nameWithInitials' => $student?->nameWithInitials,
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

    /**
     * Get student marks grades counts by teacherId and year,
     * grouped by grade/class/subject with class teacher info.
     */
    public function getTeacherStatsByYear(int $teacherId, string $year): JsonResponse
    {
        // Get all classes where this teacher is the class teacher for the given year
        $classTeachers = ComClassTeacher::query()
            ->with(['class', 'grade', 'teacher'])
            ->where('teacherId', $teacherId)
            ->where('year', $year)
            ->get();

        // Get all student marks created by this teacher for the given year
        $teacherMarks = StudentMarks::query()
            ->with(['studentProfile.grade', 'studentProfile.class', 'subject'])
            ->where('createdByTeacher', $teacherId)
            ->where('academicYear', $year)
            ->get();

        // Define all possible mark grades
        $allGrades = ['A', 'B', 'C', 'S', 'W'];

        // Group marks by grade, class, and subject
        $groupedStats = [];

        foreach ($teacherMarks as $mark) {
            $profile = $mark->studentProfile;
            $subject = $mark->subject;
            if (! $profile || ! $subject) {
                continue;
            }

            $gradeId = $profile->academicGradeId;
            $classId = $profile->academicClassId;
            $subjectId = $mark->academicSubjectId;
            $key = "{$gradeId}_{$classId}_{$subjectId}";

            if (! isset($groupedStats[$key])) {
                // Find if this teacher is the class teacher for this grade/class
                $classTeacher = $classTeachers->first(function ($ct) use ($gradeId, $classId) {
                    return $ct->gradeId == $gradeId && $ct->classId == $classId;
                });

                $groupedStats[$key] = [
                    'year'             => $year,
                    'gradeId'          => $gradeId,
                    'gradeName'        => $profile->grade?->grade ?? null,
                    'classId'          => $classId,
                    'className'        => $profile->class?->className ?? null,
                    'subjectId'        => $subjectId,
                    'subjectName'      => $subject->subjectName ?? null,
                    'subjectColorCode' => $subject->colorCode ?? null,
                    'isClassTeacher'   => $classTeacher !== null,
                    'classTeacherId'   => $classTeacher?->id ?? null,
                    'classTeacherData' => $classTeacher ? [
                        'id'        => $classTeacher->id,
                        'teacherId' => $classTeacher->teacherId,
                        'teacher'   => $classTeacher->teacher ? [
                            'id'   => $classTeacher->teacher->id,
                            'name' => $classTeacher->teacher->name,
                        ] : null,
                    ] : null,
                    'totalMarks'       => 0,
                    'gradeCounts'      => array_fill_keys($allGrades, 0),
                    'termStats'        => [
                        'Term 1' => ['total' => 0, 'gradeCounts' => array_fill_keys($allGrades, 0)],
                        'Term 2' => ['total' => 0, 'gradeCounts' => array_fill_keys($allGrades, 0)],
                        'Term 3' => ['total' => 0, 'gradeCounts' => array_fill_keys($allGrades, 0)],
                    ],
                ];
            }

            // Count the mark
            $groupedStats[$key]['totalMarks']++;

            // Count by mark grade
            $markGrade = strtoupper((string) $mark->markGrade);
            if (in_array($markGrade, $allGrades)) {
                $groupedStats[$key]['gradeCounts'][$markGrade]++;
            }

            // Count by term
            $term = $mark->academicTerm;
            if (isset($groupedStats[$key]['termStats'][$term])) {
                $groupedStats[$key]['termStats'][$term]['total']++;
                if (in_array($markGrade, $allGrades)) {
                    $groupedStats[$key]['termStats'][$term]['gradeCounts'][$markGrade]++;
                }
            }
        }

        // Convert to array and sort by grade, class, then subject
        $result = collect($groupedStats)->values()->sortBy([
            ['gradeName', 'asc'],
            ['className', 'asc'],
            ['subjectName', 'asc'],
        ])->values()->all();

        return response()->json(
            $result,
        );
    }
}
