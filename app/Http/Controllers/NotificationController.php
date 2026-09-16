<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index(Request $request) {
        $notifications = Notification::where('user_id', $request->user()->user_id)
            ->latest()->limit(50)->get();
        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, $id = null) {
        if ($id) {
            Notification::where('notification_id', $id)
                ->where('user_id', $request->user()->user_id)
                ->update(['is_read' => true]);
        } else {
            Notification::where('user_id', $request->user()->user_id)->update(['is_read' => true]);
        }
        return back()->with('success', 'Marked as read.');
    }
}