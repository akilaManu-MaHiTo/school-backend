<?php
namespace App\Http\Controllers\CommonControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Factory\FactoryRequest;
use App\Repositories\All\Factory\FactoryInterface;

class FactoryController extends Controller
{
    protected $factoryInterface;

    public function __construct(FactoryInterface $factoryInterface)
    {
        $this->factoryInterface = $factoryInterface;
    }

    public function show()
    {
        $factoryDetails = $this->factoryInterface->all();

       

        return response()->json($factoryDetails);
    }
    public function store(FactoryRequest $request)
    {
        $validatedData = $request->validated();
        $factoryDetail = $this->factoryInterface->create($validatedData);

        return response()->json([
            'message'        => 'Factory detail created successfully!',
            'factory_detail' => $factoryDetail,
        ], 201);
    }

}
