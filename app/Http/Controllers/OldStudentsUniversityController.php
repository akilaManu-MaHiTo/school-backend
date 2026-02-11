<?php

namespace App\Http\Controllers;

use App\Http\Requests\OldStudentsUniversity\OldStudentsUniversityRequest;
use App\Models\OldStudentsUniversity;
use Illuminate\Http\JsonResponse;

class OldStudentsUniversityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $universities = OldStudentsUniversity::orderByDesc('created_at')->get();

        return response()->json($universities, 200);
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
    public function store(OldStudentsUniversityRequest $request): JsonResponse
    {
        $data = $request->validated();

        $university = OldStudentsUniversity::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Old student university record created successfully.',
            'data'    => $university,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $university = OldStudentsUniversity::find($id);

        if (! $university) {
            return response()->json([
                'success' => false,
                'message' => 'Old student university record not found.',
            ], 404);
        }

        return response()->json($university, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OldStudentsUniversity $oldStudentsUniversity)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OldStudentsUniversityRequest $request, int $id): JsonResponse
    {
        $university = OldStudentsUniversity::find($id);

        if (! $university) {
            return response()->json([
                'success' => false,
                'message' => 'Old student university record not found.',
            ], 404);
        }

        $data = $request->validated();

        $university->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Old student university record updated successfully.',
            'data'    => $university,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $university = OldStudentsUniversity::find($id);

        if (! $university) {
            return response()->json([
                'success' => false,
                'message' => 'Old student university record not found.',
            ], 404);
        }

        $university->delete();

        return response()->json([
            'success' => true,
            'message' => 'Old student university record deleted successfully.',
        ], 200);
    }

    /**
     * Get all university records for a given student ID.
     */
    public function getStudentUniversitiesByStudentId(int $studentId): JsonResponse
    {
        $universities = OldStudentsUniversity::where('studentId', $studentId)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($universities, 200);
    }
}
