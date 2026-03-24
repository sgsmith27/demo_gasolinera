<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class Audit
{
    public static function log(
        string $module,
        string $action,
        ?string $entityType = null,
        $entityId = null,
        ?string $description = null,
        ?array $meta = null
    ): void {
        AuditLog::create([
            'event_at' => now(),
            'user_id' => Auth::id(),
            'module' => $module,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'meta' => $meta,
        ]);
    }
}