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

        $gradeModel = ComGrades::find($gradeId);
        $classModel = ComClassMng::find($classId);

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
        $validMarks = $marks
            ->where('isAbsentStudent', false)
            ->filter(function (StudentMarks $mark) {
                return $mark->studentMark !== null;
            });

        $marksByStudentProfile = $validMarks->groupBy('studentProfileId');

        $studentAverages = [];

        foreach ($studentProfiles as $profile) {
            $profileMarks = $marksByStudentProfile->get($profile->id, collect());

            $total = $profileMarks->sum(function (StudentMarks $m) {
                return (float) $m->studentMark;
            });
            $count = $profileMarks->count();
            $avg   = $count > 0 ? $total / $count : 0.0;

            $studentAverages[] = [
                'studentProfileId' => (int) $profile->id,
                'studentId'        => (int) $profile->studentId,
                'average'          => $avg,
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
