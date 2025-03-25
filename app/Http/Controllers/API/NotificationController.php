<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends BaseController
{
    public function getUserNotifications($userId)
    {
        // Validate if the user exists
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Fetch notifications for the user with pagination
        $notifications = Notification::where('user_id', $userId)
            ->orderBy('notification_date', 'desc') // Order by notification_date in descending order
            ->paginate(10); // You can adjust the per page value as needed

        // Return paginated notifications along with pagination details
        return response()->json([
            'data' => $notifications->items(),
            'pagination' => [
                'total' => $notifications->total(),
                'count' => $notifications->count(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
                'total_pages' => $notifications->lastPage(),
            ],
        ]);
    }

    public function addNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:booking_confirmed,booking_cancelled',
            'message' => 'required|string',
            'notification_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // If notification_date is not passed, it'll use the current timestamp
        $notification = Notification::create($request->all());

        return response()->json($notification, 201);
    }
}
