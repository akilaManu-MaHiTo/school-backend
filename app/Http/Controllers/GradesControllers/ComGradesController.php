<?php

namespace App\Http\Controllers\GradesControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComGrades\ComGradesRequest;
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
        $created = $this->comGradesInterface->create($data);
        return response()->json($created, 201);
    }
}
