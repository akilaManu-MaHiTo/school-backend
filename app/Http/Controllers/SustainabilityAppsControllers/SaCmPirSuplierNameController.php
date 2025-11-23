<?php

namespace App\Http\Controllers\SustainabilityAppsControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaCmPirSuplierName\PirSuplierNameRequest;
use App\Repositories\All\SaCmPirSuplierName\SuplierNameInterface;
use Illuminate\Http\Request;

class SaCmPirSuplierNameController extends Controller
{
    protected $suplierNameInterface;

    public function __construct(SuplierNameInterface $suplierNameInterface)
    {
        $this->suplierNameInterface = $suplierNameInterface;
    }

    public function index()
    {
        $suplierName = $this->suplierNameInterface->all();
        return response()->json($suplierName);
    }

    public function store(PirSuplierNameRequest $request)
    {
        $data    = $request->validated();
        $supplierName = $this->suplierNameInterface->create($data);
        return response()->json([
            'message' => 'Supplier name created successfully',
            'data'    => $supplierName,
        ], 201);
    }
}
