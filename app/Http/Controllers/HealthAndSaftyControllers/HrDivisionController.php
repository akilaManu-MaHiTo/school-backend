<?php
namespace App\Http\Controllers\HealthAndSaftyControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\HRDivision\HRDivisionRequest;
use App\Repositories\All\HRDivision\HRDivisionInterface;

class HrDivisionController extends Controller
{
    protected HRDivisionInterface $hrDivisionInterface;

    public function __construct(HRDivisionInterface $hrDivisionInterface)
    {
        $this->hrDivisionInterface = $hrDivisionInterface;
    }

    public function index()
    {
        $divisions = $this->hrDivisionInterface->All();
        return response()->json($divisions, 200);
    }

    public function store(HRDivisionRequest $request)
    {
        $data = $request->validated();
        $created = $this->hrDivisionInterface->create($data);
        return response()->json($created, 201);
    }
}
