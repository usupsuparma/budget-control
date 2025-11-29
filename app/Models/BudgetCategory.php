<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'parent_id',
        'level',
        'sort_order',
        'is_active',
        'description',
    ];

    protected $casts = [
        'level' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function parent()
    {
        return $this->belongsTo(BudgetCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(BudgetCategory::class, 'parent_id')
                    ->orderBy('sort_order');
    }

    public function budgetItems()
    {
        return $this->hasMany(WorkplanBudgetItem::class, 'budget_category_id');
    }

    // Scopes
    public function scopeParentOnly($query)
    {
        return $query->where('level', 1)
                     ->whereNull('parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Helper Methods
    public function isParent(): bool
    {
        return $this->level === 1 && $this->parent_id === null;
    }

    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    public function getFullNameAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->name . ' - ' . $this->name;
        }
        return $this->name;
    }
}
