<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $guarded = [];

    public function category(): BelongsTo
    {
        return $this->belongsTo(NotificationCategory::class, 'category_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(NotificationRead::class);
    }

    public function isReadBy(Employee $employee): bool
    {
        return $this->reads()->where('employee_id', $employee->id)->where('is_read', true)->exists();
    }
}
