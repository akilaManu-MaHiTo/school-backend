<?php

namespace App\Http\Controllers;

use App\Http\Requests\OldStudentsOccupation\OldStudentsOccupationRequest;
use App\Models\OldStudentsOccupation;
use Illuminate\Http\JsonResponse;

class OldStudentsOccupationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $occupations = OldStudentsOccupation::orderByDesc('created_at')->get();

        return response()->json($occupations, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OldStudentsOccupationRequest $request): JsonResponse
    {
        $data = $request->validated();

        $occupation = OldStudentsOccupation::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Old student occupation record created successfully.',
            'data'    => $occupation,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $occupation = OldStudentsOccupation::find($id);

        if (! $occupation) {
            return response()->json([
                'success' => false,
                'message' => 'Old student occupation record not found.',
            ], 404);
        }

        return response()->json($occupation, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OldStudentsOccupationRequest $request, int $id): JsonResponse
    {
        $occupation = OldStudentsOccupation::find($id);

        if (! $occupation) {
            return response()->json([
                'success' => false,
                'message' => 'Old student occupation record not found.',
            ], 404);
        }

        $data = $request->validated();

        $occupation->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Old student occupation record updated successfully.',
            'data'    => $occupation,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $occupation = OldStudentsOccupation::find($id);

        if (! $occupation) {
            return response()->json([
                'success' => false,
                'message' => 'Old student occupation record not found.',
            ], 404);
        }

        $occupation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Old student occupation record deleted successfully.',
        ], 200);
    }

    /**
     * Get all occupation records for a given student ID.
     */
    public function getStudentOccupationsByStudentId(int $studentId): JsonResponse
    {
        $occupations = OldStudentsOccupation::where('studentId', $studentId)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($occupations, 200);
    }
}
