<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffMember extends Model
{
    /** Bodies a person can belong to, used by group_label. */
    public const ROLES = [
        'leadership' => 'University Leadership',
        'dean' => 'Dean',
        'lecturer' => 'Academic Staff',
        'administrative' => 'Administrative Staff',
        'council' => 'University Council',
        'guild' => 'Students\' Guild',
        'committee' => 'Committee Member',
        'other' => 'Staff',
    ];

    protected $fillable = [
        'name', 'title', 'staff_role', 'group_label', 'faculty_id', 'department',
        'email', 'phone', 'bio', 'education', 'photo', 'sort_order', 'is_published',
    ];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->orderBy('sort_order')->orderBy('name');
    }

    public function roleLabel(): string
    {
        return self::ROLES[$this->staff_role] ?? ucfirst((string) $this->staff_role);
    }
}
