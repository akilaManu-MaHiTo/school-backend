<?php

namespace App\Http\Controllers\GradesControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComGrades\ComGradesRequest;
use App\Models\ComGrades;
use App\Repositories\All\ComGrades\ComGradesInterface;

class ComGradesController extends Controller
{
    protected ComGradesInterface $comGradesInterface;

    public function __construct(ComGradesInterface $comGradesInterface)
    {
        $this->comGradesInterface = $comGradesInterface;
    }

    public function index()
    {
        $grades = $this->comGradesInterface->All();
        return response()->json($grades, 200);
    }

    public function store(ComGradesRequest $request)
    {
        $data = $request->validated();
        $grade = isset($data['grade']) ? (int) $data['grade'] : null;

        if ($grade === null) {
            return response()->json([
                'success' => false,
                'message' => 'Grade is required.',
            ], 422);
        }

        if ($grade < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Grade must be at least 1.',
            ], 422);
        }

        if ($grade > 13) {
            return response()->json([
                'success' => false,
                'message' => 'Grade cannot be greater than 13.',
            ], 422);
        }

        $exists = false;
        if (method_exists($this->comGradesInterface, 'existsByGrade')) {
            $exists = $this->comGradesInterface->existsByGrade($grade);
        } else {
            $exists = ComGrades::where('grade', $grade)->exists();
        }

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This Grade Already Exists.'
            ], 409);
        }
        $created = $this->comGradesInterface->create($data);
        return response()->json($created, 201);
    }

    public function update(ComGradesRequest $request, $id)
    {
        $data = $request->validated();
        $grade = isset($data['grade']) ? (int) $data['grade'] : null;

        if ($grade === null) {
            return response()->json([
                'success' => false,
                'message' => 'Grade is required.',
            ], 422);
        }
        if ($grade < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Grade must be at least 1.',
            ], 422);
        }
        if ($grade > 13) {
            return response()->json([
                'success' => false,
                'message' => 'Grade cannot be greater than 13.',
            ], 422);
        }
        $exists = ComGrades::where('grade', $grade)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This Grade Already Exists.'
            ], 409);
        }

        $updated = $this->comGradesInterface->update($id, $data);
        return response()->json([
            'success' => true,
            'message' => 'Grade updated successfully.',
            'data' => $updated
        ], 200);
    }
}
