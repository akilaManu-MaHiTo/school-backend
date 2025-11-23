<?php

namespace App\Http\Controllers\SustainabilityAppsControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaCmCmrUseOfPPE\CmrUseOfPPERequest;
use App\Repositories\All\SaCmCmrUseOfPPE\CmrUseOfPPEInterface;

class SaCmCmrUseOfPPEController extends Controller
{
    protected $cmrUseOfPPEInterface;

    public function __construct(CmrUseOfPPEInterface $cmrUseOfPPEInterface)
    {
        $this->cmrUseOfPPEInterface = $cmrUseOfPPEInterface;
    }

    public function index()
    {
        $recode = $this->cmrUseOfPPEInterface->all();
        return response()->json($recode);
    }

    public function store(CmrUseOfPPERequest $request)
    {
        $data    = $request->validated();
        $recode = $this->cmrUseOfPPEInterface->create($data);
        return response()->json([
            'message' => 'Use of PPE created successfully',
            'data'    => $recode,
        ], 201);
    }
}
