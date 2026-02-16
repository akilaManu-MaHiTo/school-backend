<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OldStudentsController extends Controller
{
    /**
     * Display a listing of old students with university and occupation details.
     *
     * Optional query param: `search`.
     * If `search` is null/empty, returns all old students.
     */
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('search', ''));

        // Select all users marked as old students, regardless of
        // whether they have university or occupation records.
        $query = User::with(['oldUniversities', 'oldOccupations'])
            ->where('employeeType', 'OldStudent');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nameWithInitials', 'like', "%{$search}%")
                    ->orWhere('userName', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('oldOccupations', function ($oq) use ($search) {
                        $oq->where('occupation', 'like', "%{$search}%")
                            ->orWhere('companyName', 'like', "%{$search}%")
                            ->orWhere('country', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%");
                    })
                    ->orWhereHas('oldUniversities', function ($uq) use ($search) {
                        $uq->where('universityName', 'like', "%{$search}%")
                            ->orWhere('degree', 'like', "%{$search}%")
                            ->orWhere('faculty', 'like', "%{$search}%")
                            ->orWhere('country', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%");
                    });
            });
        }

        $students = $query->orderBy('nameWithInitials')->get();

        return response()->json($students, 200);
    }
}
