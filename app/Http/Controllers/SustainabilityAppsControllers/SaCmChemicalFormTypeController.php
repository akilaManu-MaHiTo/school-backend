<?php

namespace App\Http\Controllers\SustainabilityAppsControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaCmCmrChemicalFormType\CmrChemicalFormTypeRequest;
use App\Repositories\All\SaCmCmrChemicalFormType\ChemicalFormTypeInterface;

class SaCmChemicalFormTypeController extends Controller
{
    protected $chemicalFormTypeInterface;

    public function __construct(ChemicalFormTypeInterface $chemicalFormTypeInterface)
    {
        $this->chemicalFormTypeInterface = $chemicalFormTypeInterface;
    }

    public function index()
    {
        $chemical = $this->chemicalFormTypeInterface->all();
        return response()->json($chemical);
    }

    public function store(CmrChemicalFormTypeRequest $request)
    {
        $data    = $request->validated();
        $chemical = $this->chemicalFormTypeInterface->create($data);
        return response()->json([
            'message' => 'Chemical Form Type created successfully',
            'data'    => $chemical,
        ], 201);
    }
}
