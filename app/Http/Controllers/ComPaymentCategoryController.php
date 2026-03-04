<?php

namespace App\Http\Controllers;

use App\Models\ComPaymentCategory;
use App\Http\Requests\ComPaymentCategory\ComPaymentCategoryRequest;
use Illuminate\Support\Facades\Auth;

class ComPaymentCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = ComPaymentCategory::with(['createdByUser'])->get();
        return response()->json($items, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ComPaymentCategoryRequest $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        $data = $request->validated();
        $data['createdBy'] = $userId;
        $exists = ComPaymentCategory::where('categoryName', $data['categoryName'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This payment category already exists.',
            ], 409);
        }

        $created = ComPaymentCategory::create($data);

        return response()->json($created->load(['createdByUser']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = ComPaymentCategory::with(['createdByUser'])->find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Payment category not found.',
            ], 404);
        }

        return response()->json($item, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ComPaymentCategoryRequest $request, string $id)
    {
        $item = ComPaymentCategory::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Payment category not found.',
            ], 404);
        }

        $data = $request->validated();

        $exists = ComPaymentCategory::where('categoryName', $data['categoryName'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This payment category already exists.',
            ], 409);
        }

        $item->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Payment category updated successfully.',
            'data' => $item->load(['createdByUser']),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = ComPaymentCategory::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Payment category not found.',
            ], 404);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment category deleted successfully.',
        ], 200);
    }
}
