<?php

namespace App\Http\Controllers;

use App\Models\ComClassTeacher;
use App\Http\Requests\ComClassTeacher\ComClassTeacherRequest;

class ComClassTeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = ComClassTeacher::with(['class', 'teacher','grade'])->get();
        return response()->json($items, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ComClassTeacherRequest $request)
    {
        $data = $request->validated();

        $exists = ComClassTeacher::where('classId', $data['classId'])
            ->where('year', $data['year'])
            ->where('gradeId', $data['gradeId'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This Clas is already has a teacher assigned for the specified year.',
            ], 409);
        }

        $created = ComClassTeacher::create($data);

        return response()->json($created->load(['class', 'teacher']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = ComClassTeacher::with(['class', 'teacher'])->find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Class teacher assignment not found.',
            ], 404);
        }

        return response()->json($item, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ComClassTeacherRequest $request, string $id)
    {
        $item = ComClassTeacher::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Class teacher assignment not found.',
            ], 404);
        }

        $data = $request->validated();

        $exists = ComClassTeacher::where('classId', $data['classId'])
            ->where('year', $data['year'])
            ->where('gradeId', $data['gradeId'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This teacher is already assigned to this class and year.',
            ], 409);
        }

        $item->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Class teacher assignment updated successfully.',
            'data' => $item->load(['class', 'teacher']),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = ComClassTeacher::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Class teacher assignment not found.',
            ], 404);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Class teacher assignment deleted successfully.',
        ], 200);
    }
}
