<?php
use App\Models\{Notification, AuditLog, MaintenanceRequest};

if (!function_exists('notify')) {
    function notify($userId, $title, $message, $type = 'info', $link = null) {
        return Notification::create([
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
            'link'    => $link,
        ]);
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
            ->orderByRaw("FIELD(priority, 'urgent','high','medium','low')")
            ->orderBy('date_reported', 'asc')
            ->get();
        foreach ($requests as $i => $r) {
            $r->update(['queue_position' => $i + 1]);
        }
    }
}