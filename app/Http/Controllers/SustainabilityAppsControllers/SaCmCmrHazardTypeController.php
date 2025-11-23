<?php

namespace App\Http\Controllers\SustainabilityAppsControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaCmCmrHazardType\CmrHazardTypeRequest;
use App\Repositories\All\SaCmCmrHazardType\CmrHazardTypeInterface;
use Illuminate\Http\Request;

class SaCmCmrHazardTypeController extends Controller
{
    protected $cmrHazardTypeInterface;

    public function __construct(CmrHazardTypeInterface $cmrHazardTypeInterface)
    {
        $this->cmrHazardTypeInterface = $cmrHazardTypeInterface;
    }

    public function index()
    {
        $type = $this->cmrHazardTypeInterface->all();
        return response()->json($type);
    }

    public function store(CmrHazardTypeRequest $request)
    {
        $data    = $request->validated();
        $type = $this->cmrHazardTypeInterface->create($data);
        return response()->json([
            'message' => 'Hazard Type created successfully',
            'data'    => $type,
        ], 201);
    }
}
