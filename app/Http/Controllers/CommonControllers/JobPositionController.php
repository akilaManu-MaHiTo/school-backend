<?php
namespace App\Http\Controllers\CommonControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobPosition\JobPositionRequest;
use App\Repositories\All\ComJobPosition\JobPositionInterface;

class JobPositionController extends Controller
{
    protected $jobPositionInterface;

    public function __construct(JobPositionInterface $jobPositionInterface)
    {
        $this->jobPositionInterface = $jobPositionInterface;
    }

    public function index()
    {
        $jobPositions = $this->jobPositionInterface->All();
        
        return response()->json($jobPositions);
    }

    public function store(JobPositionRequest $request)
    {
        $jobPosition = $this->jobPositionInterface->create($request->validated());

        return response()->json([
            'message'     => 'JobPosition created successfully!',
            'jobPosition' => $jobPosition,
        ], 201);
    }
}
