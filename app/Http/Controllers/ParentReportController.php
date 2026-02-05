<?php

namespace App\Http\Controllers;

use App\Models\ComClassMng;
use App\Models\ComGrades;
use App\Models\ComStudentProfile;
use App\Models\StudentMarks;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class ParentReportController extends Controller
{
    public function getStudentWeekSubjectDetails(int $studentId, string $year, string $examType): JsonResponse
    {
        $standardTerms = ['Term 1', 'Term 2', 'Term 3'];

        if ($examType !== 'All' && ! in_array($examType, $standardTerms, true)) {
            return response()->json([
                'message' => 'Invalid examType. Use Term 1, Term 2, Term 3, or All.',
            ], 422);
        }

        $studentProfile = ComStudentProfile::query()
            ->where('studentId', $studentId)
            ->where('academicYear', $year)
            ->first();

        if (! $studentProfile) {
            return response()->json([], 404);
        }

        $gradeId = (int) $studentProfile->academicGradeId;
        $classId = (int) $studentProfile->academicClassId;

        $studentProfiles = ComStudentProfile::query()
            ->where('academicGradeId', $gradeId)
            ->where('academicClassId', $classId)
            ->where('academicYear', $year)
            ->get();

        if ($studentProfiles->isEmpty()) {
            return response()->json([]);
        }

        // Pre-load marks for each standard term for the whole class
        $marksByTerm = [];
        foreach ($standardTerms as $term) {
            $marksByTerm[$term] = $this->getMarksForProfiles($studentProfiles, $year, $term);
        }

        $previousMarksBySubject = [];
        $termRows               = [];

        foreach ($standardTerms as $term) {
            /** @var Collection<int, StudentMarks> $marks */
            $marks = $marksByTerm[$term];

            $weakSubjects = [];

            if (! $marks->isEmpty()) {
                $classSubjectAverages = $this->buildClassSubjectAverages($marks);

                $studentMarks = $marks->filter(function (StudentMarks $mark) use ($studentProfile) {
                    return (int) $mark->studentProfileId === (int) $studentProfile->id;
                });

                foreach ($studentMarks as $mark) {
                    $subject = $mark->subject;

                    if (! $subject) {
                        continue;
                    }

                    $subjectId   = (int) $subject->id;
                    $isAbsent    = (bool) $mark->isAbsentStudent;
                    $numericMark = $mark->studentMark !== null ? (float) $mark->studentMark : null;

                    if ($isAbsent || $numericMark === null) {
                        // Do not consider absent or null marks for weak/strong calculations,
                        // and do not update previous mark history.
                        continue;
                    }

                    $classAverage = $classSubjectAverages[$subjectId] ?? null;
                    $previousMark = $previousMarksBySubject[$subjectId] ?? null;

                    $trend = $previousMark !== null
                        ? $this->buildTrend($previousMark, $numericMark, true)
                        : null;

                    // Update history for next term's trend calculation
                    $previousMarksBySubject[$subjectId] = $numericMark;

                    if ($classAverage === null || $numericMark >= $classAverage) {
                        // Not a weak subject
                        continue;
                    }

                    $weakSubjects[] = [
                        'subjectId'             => $subjectId,
                        'subjectName'           => $subject->subjectName,
                        'colorCode'             => $subject->colorCode,
                        'studentMark'           => $numericMark,
                        'classAverageMark'      => $classAverage,
                        'differenceFromClass'   => $numericMark - $classAverage,
                        'trendFromPreviousTerm' => $trend,
                    ];
                }

                // Weakest subjects first
                usort($weakSubjects, function (array $a, array $b) {
                    return $a['differenceFromClass'] <=> $b['differenceFromClass'];
                });

                // Limit to 3 weakest subjects
                $weakSubjects = array_slice($weakSubjects, 0, 3);
            }

            $termRows[] = [
                'term'         => $term,
                'weakSubjects' => $weakSubjects,
            ];
        }

        // For single-term requests, return only the requested term row
        if ($examType !== 'All') {
            $termRows = array_values(array_filter($termRows, function (array $row) use ($examType) {
                return $row['term'] === $examType;
            }));
        }

        return response()->json([
            'studentId' => $studentId,
            'year'      => $year,
            'gradeId'   => $gradeId,
            'classId'   => $classId,
            'examType'  => $examType,
            'terms'     => $termRows,
        ]);
    }

    public function getStudentStrongSubjectDetails(int $studentId, string $year, string $examType): JsonResponse
    {
        $standardTerms = ['Term 1', 'Term 2', 'Term 3'];

        if ($examType !== 'All' && ! in_array($examType, $standardTerms, true)) {
            return response()->json([
                'message' => 'Invalid examType. Use Term 1, Term 2, Term 3, or All.',
            ], 422);
        }

        $studentProfile = ComStudentProfile::query()
            ->where('studentId', $studentId)
            ->where('academicYear', $year)
            ->first();

        if (! $studentProfile) {
            return response()->json([], 404);
        }

        $gradeId = (int) $studentProfile->academicGradeId;
        $classId = (int) $studentProfile->academicClassId;

        $studentProfiles = ComStudentProfile::query()
            ->where('academicGradeId', $gradeId)
            ->where('academicClassId', $classId)
            ->where('academicYear', $year)
            ->get();

        if ($studentProfiles->isEmpty()) {
            return response()->json([]);
        }

        // Pre-load marks for each standard term for the whole class
        $marksByTerm = [];
        foreach ($standardTerms as $term) {
            $marksByTerm[$term] = $this->getMarksForProfiles($studentProfiles, $year, $term);
        }

        $previousMarksBySubject = [];
        $termRows               = [];

        foreach ($standardTerms as $term) {
            /** @var Collection<int, StudentMarks> $marks */
            $marks = $marksByTerm[$term];

            $strongSubjects = [];

            if (! $marks->isEmpty()) {
                $classSubjectAverages = $this->buildClassSubjectAverages($marks);

                $studentMarks = $marks->filter(function (StudentMarks $mark) use ($studentProfile) {
                    return (int) $mark->studentProfileId === (int) $studentProfile->id;
                });

                foreach ($studentMarks as $mark) {
                    $subject = $mark->subject;

                    if (! $subject) {
                        continue;
                    }

                    $subjectId   = (int) $subject->id;
                    $isAbsent    = (bool) $mark->isAbsentStudent;
                    $numericMark = $mark->studentMark !== null ? (float) $mark->studentMark : null;

                    if ($isAbsent || $numericMark === null) {
                        // Do not consider absent or null marks for weak/strong calculations,
                        // and do not update previous mark history.
                        continue;
                    }

                    $classAverage = $classSubjectAverages[$subjectId] ?? null;
                    $previousMark = $previousMarksBySubject[$subjectId] ?? null;

                    $trend = $previousMark !== null
                        ? $this->buildTrend($previousMark, $numericMark, true)
                        : null;

                    // Update history for next term's trend calculation
                    $previousMarksBySubject[$subjectId] = $numericMark;

                    if ($classAverage === null || $numericMark < $classAverage) {
                        // Not a strong subject
                        continue;
                    }

                    $strongSubjects[] = [
                        'subjectId'             => $subjectId,
                        'subjectName'           => $subject->subjectName,
                        'colorCode'             => $subject->colorCode,
                        'studentMark'           => $numericMark,
                        'classAverageMark'      => $classAverage,
                        'differenceFromClass'   => $numericMark - $classAverage,
                        'trendFromPreviousTerm' => $trend,
                    ];
                }

                // Strongest subjects first
                usort($strongSubjects, function (array $a, array $b) {
                    return $b['differenceFromClass'] <=> $a['differenceFromClass'];
                });

                // Limit to 3 strongest subjects
                $strongSubjects = array_slice($strongSubjects, 0, 3);
            }

            $termRows[] = [
                'term'           => $term,
                'strongSubjects' => $strongSubjects,
            ];
        }

        // For single-term requests, return only the requested term row
        if ($examType !== 'All') {
            $termRows = array_values(array_filter($termRows, function (array $row) use ($examType) {
                return $row['term'] === $examType;
            }));
        }

        return response()->json([
            'studentId' => $studentId,
            'year'      => $year,
            'gradeId'   => $gradeId,
            'classId'   => $classId,
            'examType'  => $examType,
            'terms'     => $termRows,
        ]);
    }

    public function getParentReportLineChart(int $studentId): JsonResponse
    {
        $marks = StudentMarks::query()
            ->with(['subject', 'studentProfile'])
            ->whereHas('studentProfile', function ($query) use ($studentId) {
                $query->where('studentId', $studentId);
            })
            ->whereIn('academicTerm', ['Term 1', 'Term 2', 'Term 3'])
            ->get();

        if ($marks->isEmpty()) {
            return response()->json([]);
        }

        $subjects = [];

        // Group first by subject, then by year inside each subject
        $groupedBySubject = $marks->groupBy('academicSubjectId');

        foreach ($groupedBySubject as $subjectId => $subjectMarks) {
            /** @var StudentMarks $first */
            $first   = $subjectMarks->first();
            $subject = $first->subject;

            if (! $subject) {
                continue;
            }

            $yearsData = [];

            $groupedByYear = $subjectMarks->groupBy('academicYear');

            foreach ($groupedByYear as $year => $yearMarks) {
                $termMarks = [
                    'Term 1' => null,
                    'Term 2' => null,
                    'Term 3' => null,
                ];

                foreach ($yearMarks as $mark) {
                    $term = (string) $mark->academicTerm;

                    if (! array_key_exists($term, $termMarks)) {
                        continue;
                    }

                    $isAbsent    = (bool) $mark->isAbsentStudent;
                    $numericMark = $mark->studentMark !== null ? (float) $mark->studentMark : null;
                    $displayMark = $isAbsent ? 'Ab' : $numericMark;

                    $termMarks[$term] = $displayMark;
                }

                $yearsData[] = [
                    'year'      => (string) $year,
                    'term1Mark' => $termMarks['Term 1'],
                    'term2Mark' => $termMarks['Term 2'],
                    'term3Mark' => $termMarks['Term 3'],
                ];
            }

            $subjects[] = [
                'subjectName' => $subject->subjectName,
                'colorCode'   => $subject->colorCode,
                'year'        => $yearsData,
            ];
        }

        return response()->json($subjects);
    }

    public function getParentReport(int $studentId, string $year, string $examType): JsonResponse
    {
        $studentProfile = ComStudentProfile::query()
            ->with(['student', 'grade', 'class'])
            ->where('studentId', $studentId)
            ->where('academicYear', $year)
            ->first();

        if (! $studentProfile) {
            return response()->json([], 404);
        }

        $gradeId = (int) $studentProfile->academicGradeId;
        $classId = (int) $studentProfile->academicClassId;

        $studentProfiles = ComStudentProfile::query()
            ->with('student')
            ->where('academicGradeId', $gradeId)
            ->where('academicClassId', $classId)
            ->where('academicYear', $year)
            ->get();

        if ($studentProfiles->isEmpty()) {
            return response()->json([]);
        }

        $gradeModel = ComGrades::find($gradeId, ['*']);
        $classModel = ComClassMng::find($classId, ['*']);

        // If "All" is requested, return Term 1, Term 2 and Term 3 details
        if ($examType === 'All') {
            $terms = [
                'Term 1',
                'Term 2',
                'Term 3',
            ];

            $responseData = [];

            foreach ($terms as $termLabel) {
                $marks = $this->getMarksForProfiles($studentProfiles, $year, $termLabel);

                if ($marks->isEmpty()) {
                    continue;
                }

                $classSubjectAverages  = $this->buildClassSubjectAverages($marks);
                $classSubjectMaxScores = $this->buildClassSubjectMaxScores($marks);
                $studentSummary        = $this->buildStudentSummary($studentProfile->id, $studentId, $marks, $classSubjectAverages, $classSubjectMaxScores);
                $positionInfo         = $this->calculateStudentPosition($studentProfiles, $marks, $studentProfile->id, $studentId);

                $data = [
                    'student' => [
                        'id'               => $studentProfile->student?->id,
                        'userName'         => $studentProfile->student?->userName,
                        'admissionNumber'  => $studentProfile->student?->employeeNumber,
                        'email'            => $studentProfile->student?->email,
                        'nameWithInitials' => $studentProfile->student?->nameWithInitials,
                    ],
                    'academicDetails' => [
                        'year'      => $year,
                        'gradeId'   => $gradeId,
                        'grade'     => $gradeModel?->grade ?? $studentProfile->grade?->grade,
                        'classId'   => $classId,
                        'className' => $classModel?->className ?? $studentProfile->class?->className,
                        'examType'  => $termLabel,
                    ],
                    'subjects' => $studentSummary['subjects'],
                    'overall'  => [
                        'averageOfMarks' => $positionInfo['average'] ?? 0.0,
                        'position'       => $positionInfo['position'] ?? null,
                    ],
                ];

                $responseData[] = $data;
            }

            return response()->json($responseData);
        }

        // Single term path
        $marks = $this->getMarksForProfiles($studentProfiles, $year, $examType);

        if ($marks->isEmpty()) {
            return response()->json([]);
        }

        $classSubjectAverages  = $this->buildClassSubjectAverages($marks);
        $classSubjectMaxScores = $this->buildClassSubjectMaxScores($marks);
        $studentSummary        = $this->buildStudentSummary($studentProfile->id, $studentId, $marks, $classSubjectAverages, $classSubjectMaxScores);
        $positionInfo         = $this->calculateStudentPosition($studentProfiles, $marks, $studentProfile->id, $studentId);

        $data = [
            'student' => [
                'id'               => $studentProfile->student?->id,
                'userName'         => $studentProfile->student?->userName,
                'admissionNumber'  => $studentProfile->student?->employeeNumber,
                'email'            => $studentProfile->student?->email,
                'nameWithInitials' => $studentProfile->student?->nameWithInitials,
            ],
            'academicDetails' => [
                'year'      => $year,
                'gradeId'   => $gradeId,
                'grade'     => $gradeModel?->grade ?? $studentProfile->grade?->grade,
                'classId'   => $classId,
                'className' => $classModel?->className ?? $studentProfile->class?->className,
                'examType'  => $examType,
            ],
            'subjects' => $studentSummary['subjects'],
            'overall'  => [
                'averageOfMarks' => $positionInfo['average'] ?? 0.0,
                'position'       => $positionInfo['position'] ?? null,
            ],
        ];

        return response()->json([$data]);
    }

    public function getStudentClassAverage(int $studentId, string $year, string $examType): JsonResponse
    {
        $standardTerms = ['Term 1', 'Term 2', 'Term 3'];

        if ($examType !== 'All' && ! in_array($examType, $standardTerms, true)) {
            return response()->json([
                'message' => 'Invalid examType. Use Term 1, Term 2, Term 3, or All.',
            ], 422);
        }

        $studentProfile = ComStudentProfile::query()
            ->where('studentId', $studentId)
            ->where('academicYear', $year)
            ->first();

        if (! $studentProfile) {
            return response()->json([], 404);
        }

        $gradeId = (int) $studentProfile->academicGradeId;
        $classId = (int) $studentProfile->academicClassId;

        $studentProfiles = ComStudentProfile::query()
            ->where('academicGradeId', $gradeId)
            ->where('academicClassId', $classId)
            ->where('academicYear', $year)
            ->get();

        if ($studentProfiles->isEmpty()) {
            return response()->json([]);
        }

        // If a single term is requested (Term 2/Term 3) we still fetch the previous term
        // so that a trend can be computed.
        $termsToProcess = $examType === 'All' ? $standardTerms : [$examType];

        if ($examType !== 'All') {
            $previous = $this->getPreviousStandardTerm($examType);
            if ($previous !== null) {
                $termsToProcess = [$previous, $examType];
            }
        }

        $termRows = [];
        $previousRow = null;

        foreach ($termsToProcess as $term) {
            $marks = $this->getMarksForProfiles($studentProfiles, $year, $term);

            $positionInfo = $marks->isEmpty()
                ? ['average' => null, 'position' => null]
                : $this->calculateStudentPosition($studentProfiles, $marks, (int) $studentProfile->id, $studentId);

            $classAverage = $marks->isEmpty()
                ? null
                : $this->calculateClassAverageFromMarks($studentProfiles, $marks);

            $row = [
                'term'           => $term,
                'studentAverage' => $positionInfo['average'],
                'studentPosition'=> $positionInfo['position'],
                'classAverage'   => $classAverage,
                'trend'          => [
                    // Trend is relative to the previous standard term.
                    'fromTerm'       => $previousRow['term'] ?? null,
                    'position'       => $this->buildTrend(
                        $previousRow['studentPosition'] ?? null,
                        $positionInfo['position'],
                        false
                    ),
                    'studentAverage' => $this->buildTrend(
                        $previousRow['studentAverage'] ?? null,
                        $positionInfo['average'],
                        true
                    ),
                    'classAverage'   => $this->buildTrend(
                        $previousRow['classAverage'] ?? null,
                        $classAverage,
                        true
                    ),
                ],
            ];

            $termRows[] = $row;
            $previousRow = $row;
        }

        // For single-term requests where we loaded the previous term for trend,
        // return only the requested term row.
        if ($examType !== 'All' && count($termRows) === 2) {
            $termRows = [$termRows[1]];
        }

        return response()->json([
            'studentId' => $studentId,
            'year'      => $year,
            'gradeId'   => $gradeId,
            'classId'   => $classId,
            'examType'  => $examType,
            'terms'     => $termRows,
        ]);
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
     * @param Collection<int, ComStudentProfile> $studentProfiles
     * @param Collection<int, StudentMarks> $marks
     */
    private function calculateClassAverageFromMarks(Collection $studentProfiles, Collection $marks): float
    {
        $profileAverages = $this->buildProfileAverages($studentProfiles, $marks);
        $count = count($profileAverages);

        if ($count === 0) {
            return 0.0;
        }

        return array_sum($profileAverages) / $count;
    }

    /**
     * @param Collection<int, ComStudentProfile> $studentProfiles
     * @param Collection<int, StudentMarks> $marks
     * @return array<int, float> keyed by studentProfileId
     */
    private function buildProfileAverages(Collection $studentProfiles, Collection $marks): array
    {
        $validMarks = $marks
            ->where('isAbsentStudent', false)
            ->filter(function (StudentMarks $mark) {
                return $mark->studentMark !== null;
            });

        $marksByStudentProfile = $validMarks->groupBy('studentProfileId');

        $averages = [];

        foreach ($studentProfiles as $profile) {
            $profileMarks = $marksByStudentProfile->get($profile->id, collect());

            $total = $profileMarks->sum(function (StudentMarks $m) {
                return (float) $m->studentMark;
            });
            $count = $profileMarks->count();

            $averages[(int) $profile->id] = $count > 0 ? $total / $count : 0.0;
        }

        return $averages;
    }

    /**
     * @param string $term
     */
    private function getPreviousStandardTerm(string $term): ?string
    {
        $order = ['Term 1', 'Term 2', 'Term 3'];
        $index = array_search($term, $order, true);

        if ($index === false || $index === 0) {
            return null;
        }

        return $order[$index - 1];
    }

    /**
     * Build a trend object comparing previous to current.
     *
     * @param int|float|null $previous
     * @param int|float|null $current
     * @param bool $higherIsBetter
     * @return array{direction: 'up'|'down'|'same', delta: int|float}|null
     */
    private function buildTrend(int|float|null $previous, int|float|null $current, bool $higherIsBetter): ?array
    {
        if ($previous === null || $current === null) {
            return null;
        }

        if ($current === $previous) {
            return ['direction' => 'same', 'delta' => 0];
        }

        $isImprovement = $higherIsBetter ? ($current > $previous) : ($current < $previous);

        return [
            'direction' => $isImprovement ? 'up' : 'down',
            'delta'     => is_int($current) && is_int($previous) ? abs($current - $previous) : abs((float) $current - (float) $previous),
        ];
    }

    /**
     * Build class-level average marks per subject, excluding absents and null marks.
     *
     * @param Collection<int, StudentMarks> $marks
     * @return array<int, float>
     */
    private function buildClassSubjectAverages(Collection $marks): array
    {
        $validMarks = $marks
            ->where('isAbsentStudent', false)
            ->filter(function (StudentMarks $mark) {
                return $mark->studentMark !== null;
            });

        $grouped = $validMarks->groupBy('academicSubjectId');

        $averages = [];

        foreach ($grouped as $subjectId => $subjectMarks) {
            $total  = $subjectMarks->sum(function (StudentMarks $m) {
                return (float) $m->studentMark;
            });
            $count  = $subjectMarks->count();
            $averages[(int) $subjectId] = $count > 0 ? $total / $count : 0.0;
        }

        return $averages;
    }

    /**
     * Build class-level highest mark and corresponding grade per subject,
     * excluding absents and null marks.
     *
     * @param Collection<int, StudentMarks> $marks
     * @return array<int, array{mark: float, grade: string|null}>
     */
    private function buildClassSubjectMaxScores(Collection $marks): array
    {
        $validMarks = $marks
            ->where('isAbsentStudent', false)
            ->filter(function (StudentMarks $mark) {
                return $mark->studentMark !== null;
            });

        $grouped = $validMarks->groupBy('academicSubjectId');

        $maxScores = [];

        foreach ($grouped as $subjectId => $subjectMarks) {
            /** @var StudentMarks|null $maxRecord */
            $maxRecord = $subjectMarks
                ->sortByDesc(function (StudentMarks $m) {
                    return (float) $m->studentMark;
                })
                ->first();

            if (! $maxRecord) {
                continue;
            }

            $maxScores[(int) $subjectId] = [
                'mark'  => (float) $maxRecord->studentMark,
                'grade' => $maxRecord->markGrade,
            ];
        }

        return $maxScores;
    }

    /**
     * Build per-subject details for the given student including class averages.
     *
     * @param int                               $studentProfileId
     * @param int                               $studentId
     * @param Collection<int, StudentMarks>     $marks
    * @param array<int, float>                 $classSubjectAverages
    * @param array<int, array{mark: float, grade: string|null}> $classSubjectMaxScores
     * @return array{subjects: array<int, array<string, mixed>>}
     */
    private function buildStudentSummary(int $studentProfileId, int $studentId, Collection $marks, array $classSubjectAverages, array $classSubjectMaxScores): array
    {
        $studentMarks = $marks->filter(function (StudentMarks $mark) use ($studentProfileId) {
            return (int) $mark->studentProfileId === $studentProfileId;
        });

        $subjects = [];

        foreach ($studentMarks as $mark) {
            $subject = $mark->subject;
            if (! $subject) {
                continue;
            }

            $subjectId   = (int) $subject->id;
            $isAbsent    = (bool) $mark->isAbsentStudent;
            $numericMark = $mark->studentMark !== null ? (float) $mark->studentMark : null;

            $displayMark = $isAbsent ? 'Ab' : $numericMark;

            $subjects[] = [
                'subjectId'        => $subjectId,
                'subjectName'      => $subject->subjectName,
                'colorCode'        => $subject->colorCode,
                'studentMark'      => $displayMark,
                'studentGrade'     => $mark->markGrade,
                'classAverageMark' => $classSubjectAverages[$subjectId] ?? 0.0,
                'highestMark'      => $classSubjectMaxScores[$subjectId]['mark'] ?? null,
                'highestGrade'     => $classSubjectMaxScores[$subjectId]['grade'] ?? null,
            ];
        }

        return [
            'subjects' => $subjects,
        ];
    }

    /**
     * Calculate the student's average and position within the class based on
     * non-absent, non-null marks for the given term.
     *
     * @param Collection<int, ComStudentProfile> $studentProfiles
     * @param Collection<int, StudentMarks>      $marks
     * @param int                                $targetStudentProfileId
     * @param int                                $targetStudentId
     * @return array{average: float|null, position: int|null}
     */
    private function calculateStudentPosition(Collection $studentProfiles, Collection $marks, int $targetStudentProfileId, int $targetStudentId): array
    {
        $profileAverages = $this->buildProfileAverages($studentProfiles, $marks);

        $studentAverages = [];

        foreach ($studentProfiles as $profile) {
            $studentAverages[] = [
                'studentProfileId' => (int) $profile->id,
                'studentId'        => (int) $profile->studentId,
                'average'          => $profileAverages[(int) $profile->id] ?? 0.0,
            ];
        }

        usort($studentAverages, function (array $a, array $b) {
            return $b['average'] <=> $a['average'];
        });

        $lastAverage  = null;
        $lastPosition = 0;

        foreach ($studentAverages as $index => &$entry) {
            $currentAverage = $entry['average'];
            if ($lastAverage === null || $currentAverage < $lastAverage) {
                $lastPosition = $index + 1;
                $lastAverage  = $currentAverage;
            }
            $entry['position'] = $lastPosition;
        }
        unset($entry);

        $target = null;
        foreach ($studentAverages as $entry) {
            if ($entry['studentProfileId'] === $targetStudentProfileId || $entry['studentId'] === $targetStudentId) {
                $target = $entry;
                break;
            }
        }

        if (! $target) {
            return [
                'average'  => null,
                'position' => null,
            ];
        }

        return [
            'average'  => (float) $target['average'],
            'position' => (int) $target['position'],
        ];
    }
}
