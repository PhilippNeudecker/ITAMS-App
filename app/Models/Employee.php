<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'business_code', 'employee_id', 'external_object_id', 'upn', 'mail',
        'first_name', 'last_name', 'display_name',
        'cost_center_id', 'ad_status', 'last_sync_at',
    ];

    protected $casts = [
        'last_sync_at' => 'datetime',
    ];

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
