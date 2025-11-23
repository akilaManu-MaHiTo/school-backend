<?php
namespace App\Http\Controllers\CommonControllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserType\UserTypeRequest;
use App\Repositories\All\ComUserType\UserTypeInterface;

class UserTypeController extends Controller
{
    protected $userTypeInterface;

    public function __construct(UserTypeInterface $userTypeInterface)
    {
        $this->userTypeInterface = $userTypeInterface;
    }

    public function index()
    {
        $userType = $this->userTypeInterface->All();
       
        return response()->json($userType);
    }

    public function store(UserTypeRequest $request)
    {
        $userType = $this->userTypeInterface->create($request->validated());

        return response()->json([
            'message'    => 'JobPosition created successfully!',
            'department' => $userType,
        ], 201);
    }
}
