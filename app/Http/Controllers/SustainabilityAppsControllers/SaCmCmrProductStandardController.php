<?php

namespace App\Http\Controllers\SustainabilityAppsControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaCmCmrProductStandard\CmrProductStandardRequest;
use App\Repositories\All\SaCmCmrProductStandard\ProductStandardInterface;
use Illuminate\Http\Request;

class SaCmCmrProductStandardController extends Controller
{
    protected $productStandardInterface;

    public function __construct(ProductStandardInterface $productStandardInterface)
    {
        $this->productStandardInterface = $productStandardInterface;
    }

    public function index()
    {
        $product = $this->productStandardInterface->all();
        return response()->json($product);
    }

    public function store(CmrProductStandardRequest $request)
    {
        $data    = $request->validated();
        $product = $this->productStandardInterface->create($data);
        return response()->json([
            'message' => 'Product Standard created successfully',
            'data'    => $product,
        ], 201);
    }
}
