<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $this->log($model, 'created', null, $model->getAttributes());
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        // Skip if only updated_at changed or ignored fields
        if ($model->wasChanged() && count($model->getChanges()) === 1 && $model->wasChanged('updated_at')) {
            return;
        }

        $this->log($model, 'updated', $model->getOriginal(), $model->getChanges());
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->log($model, 'deleted', $model->getAttributes(), null);
    }

    /**
     * Handle the Model "restored" event.
     */
    public function restored(Model $model): void
    {
        $this->log($model, 'restored', null, $model->getAttributes());
    }

    /**
     * Handle the Model "force deleted" event.
     */
    public function forceDeleted(Model $model): void
    {
        $this->log($model, 'force_deleted', $model->getAttributes(), null);
    }

    /**
     * Log the event to audit_logs table.
     */
    protected function log(Model $model, string $action, ?array $oldValues = null, ?array $newValues = null): void
    {
        // Don't log AuditLog model itself (infinite loop)
        if ($model instanceof AuditLog) {
            return;
        }

        $user = Auth::user();

        AuditLog::log([
            'user_id' => $user?->id,
            'action' => $action,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'user_role' => $user?->roles->first()?->name ?? 'system',
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
