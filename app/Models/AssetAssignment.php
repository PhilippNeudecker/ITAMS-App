<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetAssignment extends Model
{
    use Auditable, SoftDeletes;

    const TYPE_USER        = 'User';
    const TYPE_COST_CENTER = 'CostCenter';

    protected $fillable = [
        'business_code', 'asset_id',
        'assigned_from', 'assigned_to',
        'assigned_by_employee_id', 'assignment_type',
        'employee_id', 'cost_center_id',
        'employee_display_name_snapshot', 'employee_mail_snapshot',
        'cost_center_code_snapshot', 'cost_center_name_snapshot',
        'comment',
    ];

    protected $casts = [
        'assigned_from' => 'datetime',
        'assigned_to'   => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function isActive(): bool
    {
        return $this->assigned_to === null;
    }
}
