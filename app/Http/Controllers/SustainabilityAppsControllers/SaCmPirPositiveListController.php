<?php

namespace App\Http\Controllers\SustainabilityAppsControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaCmPirPositveList\PirPositveListRequest;
use App\Repositories\All\SaCmPirPositiveList\PositiveListInterface;

class SaCmPirPositiveListController extends Controller
{
    protected $positiveListInterface;

    public function __construct(PositiveListInterface $positiveListInterface)
    {
        $this->positiveListInterface = $positiveListInterface;
    }

    public function index()
    {
        $PositiveList = $this->positiveListInterface->all();
        return response()->json($PositiveList);
    }

    public function store(PirPositveListRequest $request)
    {
        $PositiveList    = $request->validated();
        $source = $this->positiveListInterface->create($PositiveList);
        return response()->json([
            'message' => 'Positive List created successfully',
            'data'    => $source,
        ], 201);
    }
}
