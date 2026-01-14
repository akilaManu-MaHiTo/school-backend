<?php

namespace App\Http\Controllers\ClassMngControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComClassMng\ComClassMngRequest;
use App\Models\ComClassMng;
use App\Repositories\All\ComClassMng\ComClassMngInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComClassMngController extends Controller
{
    protected ComClassMngInterface $comClassMngInterface;

    public function __construct(ComClassMngInterface $comClassMngInterface)
    {
        $this->comClassMngInterface = $comClassMngInterface;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $classes = $this->comClassMngInterface->All();
        $classes = $classes->sortBy('className')->values();
        return response()->json($classes, 200);
    }

    /**
     * Get classes by grade range mapped to classCategory.
     *
     * 1-5   => Primary Class
     * 6-11  => Secondary Class
     * 12-13 => Tertiary Class
     */
    public function getClassesByGrade(int $grade)
    {
        if ($grade >= 1 && $grade <= 5) {
            $category = '1 - 5 Class';
        } elseif ($grade >= 6 && $grade <= 9) {
            $category = '6 - 9 Class';
        } elseif ($grade >= 10 && $grade <= 11) {
            $category = '10 - 11 Class';
        } elseif ($grade >= 12 && $grade <= 13) {
            $category = '12 - 13 Class';
        } else {
            return response()->json([], 400);
        }

        $classes = ComClassMng::where('classCategory', $category)
            ->orderBy('className')
            ->get(['id', 'className']);

        return response()->json(
            $classes,
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ComClassMngRequest $request)
    {
        $user = Auth::user();
        $userId = $user->id;
        $data = $request->validated();
        $className = $data['className'] ?? null;

        if ($className === null || trim($className) === '') {
            return response()->json(['success' => false, 'message' => 'className is required.'], 422);
        }

        $exists = false;
        if (method_exists($this->comClassMngInterface, 'existsByClassName')) {
            $exists = $this->comClassMngInterface->existsByClassName($className);
        } else {
            $exists = ComClassMng::where('className', $className)->exists();
        }

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'This Class Already Exists.'], 409);
        }
        $data['createdBy'] = $userId;
        $created = $this->comClassMngInterface->create($data);
        return response()->json($created, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = $this->comClassMngInterface->getById($id);
        if ($item === null) {
            return response()->json(['success' => false, 'message' => 'Class not found.'], 404);
        }
        return response()->json($item, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ComClassMngRequest $request, $id)
    {
        $data = $request->validated();
        $className = $data['className'] ?? null;

        if ($className === null || trim($className) === '') {
            return response()->json(['success' => false, 'message' => 'className is required.'], 422);
        }

        $exists = ComClassMng::where('className', $className)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'This Class Already Exists.'], 409);
        }
        $userId = Auth::id();
        $data['createdBy'] = $userId;

        $updated = $this->comClassMngInterface->update($id, $data);
        return response()->json(['success' => true, 'message' => 'Class updated successfully.', 'data' => $updated], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = $this->comClassMngInterface->getById($id);
        if ($item === null) {
            return response()->json(['success' => false, 'message' => 'Class not found.'], 404);
        }
        $this->comClassMngInterface->deleteById($id);
        return response()->json(['success' => true, 'message' => 'Class deleted successfully.'], 200);
    }
}
