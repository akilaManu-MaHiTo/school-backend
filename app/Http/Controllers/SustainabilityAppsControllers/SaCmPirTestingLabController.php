<?php

namespace App\Http\Controllers\SustainabilityAppsControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaCmPirTestingLab\PirTestingLabRequest;
use App\Repositories\All\SaCmPirTestingLab\TestingLabInterface;
use Illuminate\Http\Request;

class SaCmPirTestingLabController extends Controller
{
    protected $testingLabInterface;

    public function __construct(TestingLabInterface $testingLabInterface)
    {
        $this->testingLabInterface = $testingLabInterface;
    }

    public function index()
    {
        $source = $this->testingLabInterface->all();
        return response()->json($source);
    }

    public function store(PirTestingLabRequest $request)
    {
        $data    = $request->validated();
        $source = $this->testingLabInterface->create($data);
        return response()->json([
            'message' => 'Testing Lab created successfully',
            'data'    => $source,
        ], 201);
    }
}
