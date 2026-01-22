<?php

namespace App\Http\Controllers;

use App\Models\ComTeacherDetails;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ComTeacherDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $details = ComTeacherDetails::with('teacher')->orderByDesc('created_at')->get();

        return response()->json($details, 200);
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
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $teacherId = $user->id;
        $request->merge(['teacherId' => $teacherId]);

        $data = $request->validate([
            'teacherId'               => 'required|integer|exists:users,id|unique:com_teacher_details,teacherId',
            'appointmentDate'         => 'nullable|string',
            'dateOfRegister'          => 'nullable|string',
            'civilStatus'             => 'nullable|string',
            'dateOfRetirement'        => 'nullable|string',
            'dateOfFirstRegistration' => 'nullable|string',
            'teacherTransfer'         => 'nullable|string',
            'teacherGrade'         => 'nullable|string',
            'dateOfGrade'             => 'nullable|string',
            'salaryType'              => 'nullable|string',
            'registerPostNumber'      => 'nullable|string',
            'registerPostDate'        => 'nullable|string',
            'registerSubject'         => 'nullable|string',
        ]);

        $details = ComTeacherDetails::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Teacher details created successfully.',
            'data'    => $details->load('teacher'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $details = ComTeacherDetails::with('teacher')
            ->where('teacherId', $id)
            ->first();

        if (! $details) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher details not found.',
            ], 404);
        }

        return response()->json($details, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ComTeacherDetails $comTeacherDetails)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $teacherId = $user->id;
        $request->merge(['teacherId' => $teacherId]);

        $details = ComTeacherDetails::find($id);

        if (! $details) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher details not found.',
            ], 404);
        }

        $data = $request->validate([
            'teacherId'               => 'sometimes|required|integer|exists:users,id|unique:com_teacher_details,teacherId,' . $id,
            'appointmentDate'         => 'nullable|string',
            'dateOfRegister'          => 'nullable|string',
            'civilStatus'             => 'nullable|string',
            'dateOfRetirement'        => 'nullable|string',
            'dateOfFirstRegistration' => 'nullable|string',
            'teacherTransfer'         => 'nullable|string',
            'teacherGrade'         => 'nullable|string',
            'dateOfGrade'             => 'nullable|string',
            'salaryType'              => 'nullable|string',
            'registerPostNumber'      => 'nullable|string',
            'registerPostDate'        => 'nullable|string',
            'registerSubject'         => 'nullable|string',
        ]);

        $details->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Teacher details updated successfully.',
            'data'    => $details->load('teacher'),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $details = ComTeacherDetails::find($id);

        if (! $details) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher details not found.',
            ], 404);
        }

        $details->delete();

        return response()->json([
            'success' => true,
            'message' => 'Teacher details deleted successfully.',
        ], 200);
    }
}
