<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentServiceCharges\StudentServiceChargesRequest;
use App\Models\StudentServiceCharges;
use App\Models\ComStudentProfile;
use App\Models\ComParentProfile;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentServiceChargesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $charges = StudentServiceCharges::with('student')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(
            $charges,
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudentServiceChargesRequest $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $payload = $request->validated();
        $category = $payload['chargesCategory'] ?? null;

        // Special flow for School Service Charges Fees
        if ($category === 'School Service Charges Fees') {
            $primaryStudentId = $payload['studentId'];

            // Find parent(s) linked to this student
            $parentIds = ComParentProfile::where('studentId', $primaryStudentId)
                ->pluck('parentId')
                ->unique()
                ->values();

            if ($parentIds->isNotEmpty()) {
                // Find all children (students) linked to these parent(s)
                $allChildIds = ComParentProfile::whereIn('parentId', $parentIds)
                    ->pluck('studentId')
                    ->unique()
                    ->values();

                if (! $allChildIds->contains($primaryStudentId)) {
                    $allChildIds->push($primaryStudentId);
                }

                // Exclude students who already have this charge for the same year
                $alreadyChargedIds = StudentServiceCharges::whereIn('studentId', $allChildIds)
                    ->where('chargesCategory', $category)
                    ->where('yearForCharge', $payload['yearForCharge'])
                    ->pluck('studentId')
                    ->unique();

                $pendingStudentIds = $allChildIds->diff($alreadyChargedIds)->values();

                // If there are multiple pending students, ask for confirmation first
                $confirmForChildren = $request->boolean('confirmForChildren', false);

                if (! $confirmForChildren && $pendingStudentIds->count() > 1) {
                    $parentProfiles = ComParentProfile::with([
                        'student.studentProfiles.grade',
                        'student.studentProfiles.class',
                    ])
                        ->whereIn('parentId', $parentIds)
                        ->whereIn('studentId', $pendingStudentIds)
                        ->get();

                    $children = $parentProfiles
                        ->map(function ($parentProfile) {
                            $student = $parentProfile->student;

                            if (! $student) {
                                return null;
                            }

                            return [
                                'id'               => $student->id,
                                'name'             => $student->name,
                                'userName'         => $student->userName,
                                'nameWithInitials' => $student->nameWithInitials,
                                'email'            => $student->email,
                                'employeeType'     => $student->employeeType,
                                'employeeNumber'   => $student->employeeNumber,
                                'mobile'           => $student->mobile,
                                'emailVerifiedAt'  => $student->emailVerifiedAt,
                                'userType'         => $student->userType,
                                'assigneeLevel'    => $student->assigneeLevel,
                                'profileImage'     => $student->profileImage,
                                'availability'     => $student->availability,
                                'gender'           => $student->gender,
                                'birthDate'        => $student->birthDate,
                                'address'          => $student->address,
                                'student_profiles' => $student->studentProfiles
                                    ->map(function ($profile) {
                                        $grade = $profile->grade;
                                        $class = $profile->class;

                                        return [
                                            'id'                => $profile->id,
                                            'studentId'         => $profile->studentId,
                                            'academicGradeId'   => $profile->academicGradeId,
                                            'academicClassId'   => $profile->academicClassId,
                                            'academicYear'      => $profile->academicYear,
                                            'academicMedium'    => $profile->academicMedium,
                                            'basketSubjectsIds' => $profile->basketSubjectsIds,
                                            'isStudentApproved' => $profile->isStudentApproved,
                                            'grade'             => $grade ? [
                                                'id'    => $grade->id,
                                                'grade' => $grade->grade,
                                            ] : null,
                                            'class'             => $class ? [
                                                'id'          => $class->id,
                                                'className'   => $class->className,
                                                'classCategory' => $class->classCategory,
                                            ] : null,
                                        ];
                                    })
                                    ->values(),
                            ];
                        })
                        ->filter()
                        ->values();

                    return response()->json([
                        'success'               => false,
                        'requiresConfirmation'  => true,
                        'message'               => 'This parent has multiple students without this service charge. Confirm if you want to add charges for them as well.',
                        'category'              => $category,
                        'yearForCharge'         => $payload['yearForCharge'],
                        'pendingStudentIds'     => $pendingStudentIds,
                        'children'              => $children,
                    ], 200);
                }

                // User confirmed or there is only one pending student: create charges in a transaction
                $studentIdsToCharge = collect($request->input('studentIds', []))
                    ->map(fn ($id) => (int) $id)
                    ->filter();

                if ($studentIdsToCharge->isEmpty()) {
                    $studentIdsToCharge = $pendingStudentIds;
                } else {
                    if (! $studentIdsToCharge->contains($primaryStudentId)) {
                        $studentIdsToCharge->push($primaryStudentId);
                    }

                    $studentIdsToCharge = $studentIdsToCharge
                        ->intersect($pendingStudentIds)
                        ->values();

                    if ($studentIdsToCharge->isEmpty()) {
                        $studentIdsToCharge = collect([$primaryStudentId]);
                    }
                }

                $createdCharges = DB::transaction(function () use ($studentIdsToCharge, $payload) {
                    $charges = [];

                    foreach ($studentIdsToCharge as $studentId) {
                        $data = $payload;
                        $data['studentId'] = $studentId;
                        $charges[] = StudentServiceCharges::create($data);
                    }

                    return $charges;
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Student service charge(s) created successfully.',
                    'data'    => count($createdCharges) === 1 ? $createdCharges[0] : $createdCharges,
                ], 201);
            }
        }

        // Default behaviour (other categories or no parent/children found)
        $charge = StudentServiceCharges::create($payload);

        return response()->json([
            'success' => true,
            'message' => 'Student service charge created successfully.',
            'data'    => $charge,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $charge = StudentServiceCharges::with('student')->findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Student service charge not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $charge,
        ]);
    }

    /**
     * Get all service charges for a given student ID.
     */
    public function getChargesByStudentId(int $id): JsonResponse
    {
        $charges = StudentServiceCharges::with('student')
            ->where('studentId', $id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($charges);
    }

    /**
     * Check charges for all students in a given year, grade and class
     * and return their profiles with related service charges.
     *
     * Route: GET student-service-charges/{year}/{gradeId}/{classId}/{category}/check
     * - category = "All"  -> return all charges for that year
     * - category = actual category name -> filter charges by that category
     */
    public function checkChargesByYearGradeClass(string $year, int $gradeId, int $classId, string $category): JsonResponse
    {
        $studentProfiles = ComStudentProfile::with(['student', 'grade', 'class'])
            ->where('academicYear', $year)
            ->where('academicGradeId', $gradeId)
            ->where('academicClassId', $classId)
            ->get();

        if ($studentProfiles->isEmpty()) {
            return response()->json([]);
        }

        $studentIds = $studentProfiles->pluck('studentId')->all();

        $chargesQuery = StudentServiceCharges::with('student')
            ->whereIn('studentId', $studentIds)
            ->where('yearForCharge', $year);

        if ($category !== 'All') {
            $chargesQuery->where('chargesCategory', $category);
        }

        $chargesByStudent = $chargesQuery
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('studentId');

        $data = $studentProfiles->map(function ($profile) use ($chargesByStudent) {
            $studentId = $profile->studentId;

            return [
                'student' => $profile,
                'charges' => $chargesByStudent->get($studentId) ?? [],
            ];
        });

        return response()->json(
            $data,
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StudentServiceCharges $studentServiceCharges)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentServiceChargesRequest $request, int $id): JsonResponse
    {
        try {
            $charge = StudentServiceCharges::findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Student service charge not found.',
            ], 404);
        }

        $payload = $request->validated();

        $charge->update($payload);

        $charge->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Student service charge updated successfully.',
            'data'    => $charge,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $charge = StudentServiceCharges::findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Student service charge not found.',
            ], 404);
        }

        $deleted = $charge->delete();

        return response()->json([
            'success' => $deleted,
            'message' => $deleted
                ? 'Student service charge deleted successfully.'
                : 'Failed to delete student service charge.',
        ], $deleted ? 200 : 500);
    }
}
