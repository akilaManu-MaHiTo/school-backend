<?php

namespace App\Http\Controllers\SustainabilityAppsControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaCmCmrCommercialName\CmrCommercialNameRequest;
use App\Repositories\All\SaCmCmrCommercialName\CommercialNameInterface;
use Illuminate\Http\Request;

class SaCmCmrCommercialNameController extends Controller
{
    protected $commercialNameInterface;

    public function __construct(CommercialNameInterface $commercialNameInterface)
    {
        $this->commercialNameInterface = $commercialNameInterface;
    }

    public function index()
    {
        $name = $this->commercialNameInterface->all();
        return response()->json($name);
    }

    public function store(CmrCommercialNameRequest $request)
    {
        $data    = $request->validated();
        $name = $this->commercialNameInterface->create($data);
        return response()->json([
            'message' => 'Commercial Name created successfully',
            'data'    => $name,
        ], 201);
    }
}
