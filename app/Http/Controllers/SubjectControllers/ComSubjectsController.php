<?php

namespace App\Http\Controllers\SubjectControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComSubjects\ComSubjectsRequest;

use App\Models\ComSubjects;
use App\Repositories\All\ComSubjects\ComSubjectsInterface;
use Illuminate\Http\Request;

class ComSubjectsController extends Controller
{
    protected ComSubjectsInterface $comSubjectsInterface;

    public function __construct(ComSubjectsInterface $comSubjectsInterface)
    {
        $this->comSubjectsInterface = $comSubjectsInterface;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->query('search'); // ?search=xyz

        $comSubjects = $this->comSubjectsInterface->All();

        if ($search) {
            $search = strtolower($search);

            $comSubjects = $comSubjects->filter(function ($item) use ($search) {
                return str_contains(strtolower($item->subjectName), $search)
                    || str_contains(strtolower($item->subjectCode), $search)
                    || (string)$item->id === $search;
            })->values();
        }

        // Sort by subject name
        $comSubjects = $comSubjects->sortBy('subjectName')->values();
        $letters = range('A', 'Z');

        $result = [];

        foreach ($letters as $letter) {
            // Filter subjects starting with the letter
            $filtered = $comSubjects->filter(function ($item) use ($letter) {
                return strtoupper(substr($item->subjectName, 0, 1)) === $letter;
            })->values();

            $result[] = [
                'letter' => $letter,
                'subjects' => $filtered->isEmpty() ? "No subjects" : $filtered
            ];
        }

        return response()->json($result, 200);
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
    public function store(ComSubjectsRequest $request)
    {
        $data = $request->validated();
        $comSubject = isset($data['subjectName']) ? $data['subjectName'] : null;

        if ($comSubject === null || trim($comSubject) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Subject name is required.',
            ], 422);
        }

        $exists = false;
        $codeExists = false;
        if (method_exists($this->comSubjectsInterface, 'existsBySubjectName')) {
            $codeExists = $this->comSubjectsInterface->existsBySubject($comSubject);
        } elseif (method_exists($this->comSubjectsInterface, 'existsBySubjectCode')) {
            $exists = $this->comSubjectsInterface->existByCode($comSubject);
        } else {
            $exists = ComSubjects::where('subjectName', $comSubject)->exists();
            $codeExists = ComSubjects::where('subjectCode', $comSubject)->exists();
        }

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Subject already exists.',
            ], 422);
        } else if ($codeExists) {
            return response()->json([
                'success' => false,
                'message' => 'Subject code already exists.',
            ], 422);
        }

        $comSubject = $this->comSubjectsInterface->create($data);
        return response()->json($comSubject, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(ComSubjects $comSubjects)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ComSubjects $comSubjects)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ComSubjectsRequest $request, $id)
    {
        $comSubject = $this->comSubjectsInterface->findById($id);
        if (!$comSubject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found.'
            ], 404);
        }
        $data = $request->validated();
        $comSubject = isset($data['subjectName']) ? $data['subjectName'] : null;

        if ($comSubject === null || trim($comSubject) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Subject name is required.',
            ], 422);
        }

        $exists = false;
        $codeExists = false;
        if (method_exists($this->comSubjectsInterface, 'existsBySubjectName')) {
            $codeExists = $this->comSubjectsInterface->existsBySubject($comSubject);
        } elseif (method_exists($this->comSubjectsInterface, 'existsBySubjectCode')) {
            $exists = $this->comSubjectsInterface->existByCode($comSubject);
        } else {
            $exists = ComSubjects::where('subjectName', $comSubject)->exists();
            $codeExists = ComSubjects::where('subjectCode', $comSubject)->exists();
        }

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Subject already exists.',
            ], 422);
        } else if ($codeExists) {
            return response()->json([
                'success' => false,
                'message' => 'Subject code already exists.',
            ], 422);
        }

        $comSubject = $this->comSubjectsInterface->update($id, $data);
        return response()->json([
            'success' => true,
            'message' => 'Subject updated successfully.',
            'data' => $comSubject
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $comSubjects = $this->comSubjectsInterface->getById($id);
        if (!$comSubjects) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found.'
            ], 404);
        }

        $this->comSubjectsInterface->deleteById($id);

        return response()->json([
            'success' => true,
            'message' => 'Subject deleted successfully.'
        ], 200);
    }
}
