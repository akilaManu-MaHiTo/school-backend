<?php
namespace App\Http\Controllers\CommonControllers;

use App\Http\Controllers\Controller;
use App\Repositories\All\AssigneeLevel\AssigneeLevelInterface;
use Illuminate\Http\Request;

class AssigneeLevelController extends Controller
{
    protected $assigneeLevelInterface;

    public function __construct(AssigneeLevelInterface $assigneeLevelInterface)
    {
        $this->assigneeLevelInterface = $assigneeLevelInterface;
    }

    public function index()
    {
        $assigneeLevels = $this->assigneeLevelInterface->All();
        return response()->json($assigneeLevels, 201);

    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
