<?php

namespace App\Http\Controllers;

use App\Http\Requests\StudentNotifications\StudentNotificationsRequest;
use App\Models\ComStudentProfile;
use App\Models\StudentNotifications;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentNotificationsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $notifications = StudentNotifications::with(['grade', 'class', 'createdByUser'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json(
            $notifications,
            200
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudentNotificationsRequest $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $payload = $request->validated();
        $payload['createdBy'] = $user->id;

        if (isset($payload['ignoreUserIds']) && is_array($payload['ignoreUserIds'])) {
            $payload['ignoreUserIds'] = array_values(array_map('intval', $payload['ignoreUserIds']));
        }

        $notification = StudentNotifications::create($payload);

        return response()->json([
            'success' => true,
            'message' => 'Student notification created successfully.',
            'data'    => $notification->load(['grade', 'class', 'createdByUser']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        $notification = StudentNotifications::with(['grade', 'class', 'createdByUser'])->find($id);

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Student notification not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $notification,
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StudentNotifications $studentNotifications)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentNotificationsRequest $request, int $id): JsonResponse
    {
        $notification = StudentNotifications::find($id);

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Student notification not found.',
            ], 404);
        }

        $payload = $request->validated();
        $payload['createdBy'] = Auth::id();

        if (isset($payload['ignoreUserIds']) && is_array($payload['ignoreUserIds'])) {
            $payload['ignoreUserIds'] = array_values(array_map('intval', $payload['ignoreUserIds']));
        }

        $notification->update($payload);

        return response()->json([
            'success' => true,
            'message' => 'Student notification updated successfully.',
            'data'    => $notification->load(['grade', 'class', 'createdByUser']),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $notification = StudentNotifications::find($id);

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Student notification not found.',
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student notification deleted successfully.',
        ], 200);
    }

    public function getNotificationsByStudent(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $profileQuery = ComStudentProfile::query()->where('studentId', $user->id);

        if ($request->filled('year')) {
            $profileQuery->where('academicYear', $request->input('year'));
        }
        if ($request->filled('gradeId')) {
            $profileQuery->where('academicGradeId', $request->input('gradeId'));
        }
        if ($request->filled('classId')) {
            $profileQuery->where('academicClassId', $request->input('classId'));
        }

        $profile = $profileQuery->orderByDesc('created_at')->first();

        if (! $profile) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found for the current user.',
            ], 404);
        }

        $notifications = StudentNotifications::with(['grade', 'class', 'createdByUser'])
            ->where('year', $profile->academicYear)
            ->where('gradeId', $profile->academicGradeId)
            ->where('classId', $profile->academicClassId)
            ->orderByDesc('created_at')
            ->get();

        $userId = (int) $user->id;
        $payload = $notifications->map(function (StudentNotifications $notification) use ($userId) {
            $ignored = is_array($notification->ignoreUserIds) ? $notification->ignoreUserIds : [];
            $data = $notification->toArray();
            $data['markedAsRead'] = in_array($userId, $ignored, true);

            return $data;
        });

        return response()->json(
            $payload,
            200
        );
    }

    public function getNotificationsCountByStudent(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $profileQuery = ComStudentProfile::query()->where('studentId', $user->id);

        if ($request->filled('year')) {
            $profileQuery->where('academicYear', $request->input('year'));
        }
        if ($request->filled('gradeId')) {
            $profileQuery->where('academicGradeId', $request->input('gradeId'));
        }
        if ($request->filled('classId')) {
            $profileQuery->where('academicClassId', $request->input('classId'));
        }

        $profile = $profileQuery->orderByDesc('created_at')->first();

        if (! $profile) {
            return response()->json([
                'success' => false,
                'message' => 'Student profile not found for the current user.',
            ], 404);
        }

        $count = StudentNotifications::query()
            ->where('year', $profile->academicYear)
            ->where('gradeId', $profile->academicGradeId)
            ->where('classId', $profile->academicClassId)
            ->where(function ($query) use ($user) {
                $query->whereNull('ignoreUserIds')
                    ->orWhereJsonDoesntContain('ignoreUserIds', $user->id);
            })
            ->count();

        return response()->json(
            $count,
            200
        );
    }

    public function markAsRead(int $id): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $notification = StudentNotifications::find($id);

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Student notification not found.',
            ], 404);
        }

        $ignored = is_array($notification->ignoreUserIds) ? $notification->ignoreUserIds : [];
        $userId = (int) $user->id;

        if (! in_array($userId, $ignored, true)) {
            $ignored[] = $userId;
            $notification->ignoreUserIds = array_values(array_unique($ignored));
            $notification->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
            'data'    => $notification,
        ], 200);
    }

    public function markAllNotificationAsRead(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'notificationIds' => ['required', 'array', 'min:1'],
            'notificationIds.*' => ['integer', 'distinct', 'min:1'],
        ]);

        $userId = (int) $user->id;
        $ids = array_values(array_unique(array_map('intval', $validated['notificationIds'])));

        $notifications = StudentNotifications::query()
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $updatedCount = 0;
        $missingIds = [];

        foreach ($ids as $id) {
            $notification = $notifications->get($id);
            if (! $notification) {
                $missingIds[] = $id;
                continue;
            }

            $ignored = is_array($notification->ignoreUserIds) ? $notification->ignoreUserIds : [];
            if (! in_array($userId, $ignored, true)) {
                $ignored[] = $userId;
                $notification->ignoreUserIds = array_values(array_unique($ignored));
                $notification->save();
                $updatedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifications marked as read.',
            'updatedCount' => $updatedCount,
            'missingIds' => $missingIds,
        ], 200);
    }
}
