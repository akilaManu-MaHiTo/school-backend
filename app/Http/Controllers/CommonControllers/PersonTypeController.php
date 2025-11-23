<?php
namespace App\Http\Controllers\CommonControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComPersonType\PersonTypeRequest;
use App\Repositories\All\ComPersonType\PersonTypeInterface;

class PersonTypeController extends Controller
{
    protected $personTypeInterface;

    public function __construct(PersonTypeInterface $personTypeInterface)
    {
        $this->personTypeInterface = $personTypeInterface;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $personType = $this->personTypeInterface->all();

        

        return response()->json($personType);
    }
    public function store(PersonTypeRequest $request)
    {
        $validatedData = $request->validated();
        $personType    = $this->personTypeInterface->create($validatedData);

        return response()->json([
            'message'    => 'Person type created successfully!',
            'personType' => $personType,
        ], 201);
    }
}
