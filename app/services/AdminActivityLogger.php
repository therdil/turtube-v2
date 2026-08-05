<?php

namespace App\Services;

use App\Models\AdminActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class AdminActivityLogger
{
    private ?bool $activityTableAvailable = null;

    public function record(User $admin, string $action, string $description, ?Model $subject = null, array $metadata = []): void
    {
        if (! ($this->activityTableAvailable ??= Schema::hasTable('admin_activities'))) {
            return;
        }

        AdminActivity::create([
            'admin_id' => $admin->id,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'metadata' => $metadata ?: null,
        ]);
    }
}
