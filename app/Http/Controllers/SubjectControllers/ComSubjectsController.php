<?php

namespace App\Http\Controllers\SubjectControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComSubjects\ComSubjectsRequest;

use App\Models\ComSubjects;
use App\Repositories\All\ComSubjects\ComSubjectsInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
$user = Auth::user();
$userId = $user->id;
        $subjectName = $data['subjectName'] ?? null;
        $subjectCode = $data['subjectCode'] ?? null;

        $isBasketSubject = $data['isBasketSubject'] ?? null;
        if ($isBasketSubject && (!isset($data['basketGroup']) || $data['basketGroup'] === null)) {
            return response()->json([
                'success' => false,
                'message' => 'Basket group is required when the subject is a basket subject.'
            ], 422);
        }

        if (!$subjectName || trim($subjectName) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Subject name is required.'
            ], 422);
        }

        $nameExists = ComSubjects::where('subjectName', $subjectName)
            ->where('subjectMedium', $data['subjectMedium'])
            ->exists();

        if ($nameExists) {
            return response()->json([
                'success' => false,
                'message' => 'This subject name already exists for the selected medium.'
            ], 422);
        }


        $codeExists = ComSubjects::where('subjectCode', $subjectCode)->exists();
        if ($codeExists) {
            return response()->json([
                'success' => false,
                'message' => 'Subject code already exists.'
            ], 422);
        }
        $data['createdBy'] = $userId;
        $subject = $this->comSubjectsInterface->create($data);

        return response()->json($subject, 201);
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
        $subject = $this->comSubjectsInterface->findById($id);
        if (!$subject) {
            return response()->json([
                'success' => false,
                'message' => 'Subject not found.'
            ], 404);
        }

        $data = $request->validated();

        $newName = $data['subjectName'] ?? $subject->subjectName;
        $newSubjectMedium = $data['subjectMedium'] ?? $subject->subjectMedium;
        $newCode = $data['subjectCode'] ?? $subject->subjectCode;

        $isBasketSubject = $data['isBasketSubject'] ?? null;
        if ($isBasketSubject && (!isset($data['basketGroup']) || $data['basketGroup'] === null)) {
            return response()->json([
                'success' => false,
                'message' => 'Basket group is required when the subject is a basket subject.'
            ], 422);
        }

        // Check for duplicate name + medium
        $nameExists = ComSubjects::where('subjectName', $newName)
            ->where('subjectMedium', $newSubjectMedium)
            ->where('id', '!=', $subject->id)
            ->exists();

        if ($nameExists) {
            return response()->json([
                'success' => false,
                'message' => 'This subject name already exists for the selected medium.'
            ], 422);
        }

        // Check for duplicate code
        $codeExists = ComSubjects::where('subjectCode', $newCode)
            ->where('id', '!=', $subject->id)
            ->exists();

        if ($codeExists) {
            return response()->json([
                'success' => false,
                'message' => 'Subject code already exists.'
            ], 422);
        }
        $userId = Auth::id();
        $data['createdBy'] = $userId;

        $updated = $this->comSubjectsInterface->update($id, $data);

        return response()->json([
            'success' => true,
            'message' => 'Subject updated successfully.',
            'data' => $updated
        ], 200);
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

    public function getSubjects()
    {
        $comSubjects = $this->comSubjectsInterface->All();
        $comSubjects = $comSubjects->sortBy('subjectName')->values();
        return response()->json($comSubjects, 200);
    }

    public function getSubjectsByGroup1()
    {
        $filtered = $this->filterSubjectsByGroup('Group 1');
        return response()->json($filtered, 200);
    }

    public function getSubjectsByGroup2()
    {
        $filtered = $this->filterSubjectsByGroup('Group 2');
        return response()->json($filtered, 200);
    }

    public function getSubjectsByGroup3()
    {
        $filtered = $this->filterSubjectsByGroup('Group 3');
        return response()->json($filtered, 200);
    }

    /**
     * Filter subjects that are basket subjects and belong to the given group name.
     */
    private function filterSubjectsByGroup(string $groupName)
    {
        $comSubjects = $this->comSubjectsInterface->All();

        $filtered = $comSubjects->filter(function ($item) use ($groupName) {
            $isBasket = $item->isBasketSubject === true || $item->isBasketSubject === 1 || $item->isBasketSubject === '1';
            $group = isset($item->basketGroup) ? (string)$item->basketGroup : '';
            return $isBasket && trim($group) === $groupName;
        })->sortBy('subjectName')->values();

        return $filtered;
    }
}
