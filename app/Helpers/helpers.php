<?php
use App\Models\{Notification, AuditLog, MaintenanceRequest};

if (!function_exists('notify')) {
    function notify($userId, $title, $message, $type = 'info', $link = null) {
        $n = Notification::create([
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
            'link'    => $link,
        ]);

        // Bust the cached unread count so the badge updates on next page load
        cache()->forget("notif_unread_{$userId}");

        return $n;
    }
}

if (!function_exists('audit')) {
    function audit($action, $entityType = null, $entityId = null, $details = null) {
        return AuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'details'     => $details,
            'ip_address'  => request()->ip(),
        ]);
    }
}

if (!function_exists('generate_request_code')) {
    function generate_request_code() {
        $year = date('Y');
        $count = MaintenanceRequest::whereYear('created_at', $year)->count() + 1;
        return 'REQ-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('update_queue_positions')) {
    function update_queue_positions() {
        $requests = MaintenanceRequest::whereNotIn('status', ['completed','cancelled'])
            ->orderByRaw("CASE priority
                            WHEN 'urgent' THEN 1
                            WHEN 'high'   THEN 2
                            WHEN 'medium' THEN 3
                            WHEN 'low'    THEN 4
                            ELSE 5 END")
            ->orderBy('date_reported', 'asc')
            ->get();
        foreach ($requests as $i => $r) {
            $r->update(['queue_position' => $i + 1]);
        }
    }
}