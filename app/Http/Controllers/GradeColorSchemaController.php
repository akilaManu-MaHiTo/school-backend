<?php

namespace App\Http\Controllers;

use App\Http\Requests\GradeColorSchema\GradeColorSchemaRequest;
use App\Models\GradeColorSchema;
use App\Repositories\All\GradeColorSchema\GradeColorSchemaInterface;

class GradeColorSchemaController extends Controller
{
    protected GradeColorSchemaInterface $gradeColorSchemaRepository;

    public function __construct(GradeColorSchemaInterface $gradeColorSchemaRepository)
    {
        $this->gradeColorSchemaRepository = $gradeColorSchemaRepository;
    }

    public function index()
    {
        return response()->json($this->gradeColorSchemaRepository->all(), 200);
    }

    public function store(GradeColorSchemaRequest $request)
    {
        $gradeColorSchema = $this->gradeColorSchemaRepository->create($request->validated());

        return response()->json($gradeColorSchema, 201);
    }

    public function show(GradeColorSchema $gradeColorSchema)
    {
        return response()->json($this->gradeColorSchemaRepository->getById($gradeColorSchema->id), 200);
    }

    public function update(GradeColorSchemaRequest $request, GradeColorSchema $gradeColorSchema)
    {
        $this->gradeColorSchemaRepository->update($gradeColorSchema->id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Grade color schema updated successfully.',
            'data' => $this->gradeColorSchemaRepository->getById($gradeColorSchema->id),
        ], 200);
    }

    public function destroy(GradeColorSchema $gradeColorSchema)
    {
        $this->gradeColorSchemaRepository->deleteById($gradeColorSchema->id);

        return response()->json([
            'success' => true,
            'message' => 'Grade color schema deleted successfully.',
        ], 200);
    }
}
