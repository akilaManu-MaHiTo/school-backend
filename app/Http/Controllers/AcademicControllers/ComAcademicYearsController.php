<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComAcademicYear\ComAcademicYearRequest;
use App\Models\ComAcademicYears;
use App\Repositories\All\ComAcademicYear\ComAcademicYearInterface;
use Illuminate\Http\Request;

class ComAcademicYearsController extends Controller
{
    protected ComAcademicYearInterface $comAcademicYearInterface;
    public function __construct(ComAcademicYearInterface $comAcademicYearInterface)
    {
        $this->comAcademicYearInterface = $comAcademicYearInterface;
    }
    public function index()
    {
        $year = $this->comAcademicYearInterface->All();
        $year = $year->sortBy('year')->values();
        return response()->json($year, 200);
    }


    public function store(ComAcademicYearRequest $request)
    {
        //
        $data = $request->validated();
        $year = isset($data['year']) ? (int) $data['year'] : null;

        if ($year === null) {
            return response()->json([
                'success' => false,
                'message' => 'Year is required.',
            ], 422);
        }

        $exists = false;
        if (method_exists($this->comAcademicYearInterface, 'existsByYear')) {
            $exists = $this->comAcademicYearInterface->existsByYear($year);
        } else {
            $exists = ComAcademicYears::where('year', $year)->exists();
        }
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Year already exists.',
            ], 422);
        }
        $data = $this->comAcademicYearInterface->create($data);
        return response()->json($data, 200);
    }


    public function create(Request $request)
    {
        //
    }

    public function show(ComAcademicYears $comAcademicYears)
    {
        //
    }


    public function edit(ComAcademicYears $comAcademicYears)
    {
        //
    }


    public function update(Request $request, ComAcademicYears $comAcademicYears)
    {
        //
    }


    public function destroy(ComAcademicYears $comAcademicYears)
    {
        //
    }
}
