<?php
namespace App\Http\Controllers\HealthAndSaftyControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\OhMiPiSupplierType\SupplierTypeRequest;
use App\Repositories\All\OhMiPiSupplierType\SupplierTypeInterface;

class OhMiPiMiSupplierTypeController extends Controller
{
    protected SupplierTypeInterface $supplierTypeInterface;

    public function __construct(SupplierTypeInterface $supplierTypeInterface)
    {
        $this->supplierTypeInterface = $supplierTypeInterface;
    }

    public function index()
    {
        $types = $this->supplierTypeInterface->all();
        return response()->json($types, 200);
    }

    public function store(SupplierTypeRequest $request)
    {
        $data = $request->validated();
        $created = $this->supplierTypeInterface->create($data);
        return response()->json([
            'message' => 'Supplier type created successfully',
            'data'    => $created,
        ], 201);
    }
}
