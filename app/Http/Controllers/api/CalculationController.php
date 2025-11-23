<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Summodel; // Ensure the model name is correctly referenced

class CalculationController extends Controller
{
    public function store(Request $request)
    {
        // Validate the input to ensure both 'number1' and 'number2' are provided and are numeric
        $validated = $request->validate([
            'number1' => 'required|numeric',
            'number2' => 'required|numeric',
        ]);

        // Perform the addition
        $sum = $validated['number1'] + $validated['number2'];

        // Save the result to the database
        $sumModel = new Summodel();
        $sumModel->number1 = $validated['number1'];
        $sumModel->number2 = $validated['number2'];
        $sumModel->sum = $sum;
        $sumModel->save();

        // Return the sum as a JSON response
        return response()->json([
            'success' => true,
            'sum' => $sum,
        ]);
    }
}
