<?php

namespace App\Http\Controllers\SustainabilityAppsControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaCmCmrZdhcCategory\CmrZdhcCategoryRequest;
use App\Repositories\All\SaCmCmrZdhcCategory\ZdhcCategoryInterface;
use Illuminate\Http\Request;

class SaCmCmrZdhcCategoryController extends Controller
{
    protected $zdhcCategoryInterface;

    public function __construct(ZdhcCategoryInterface $zdhcCategoryInterface)
    {
        $this->zdhcCategoryInterface = $zdhcCategoryInterface;
    }

    public function index()
    {
        $category = $this->zdhcCategoryInterface->all();
        return response()->json($category);
    }

    public function store(CmrZdhcCategoryRequest $request)
    {
        $data    = $request->validated();
        $category = $this->zdhcCategoryInterface->create($data);
        return response()->json([
            'message' => 'ZDHC Category created successfully',
            'data'    => $category,
        ], 201);
    }
}
