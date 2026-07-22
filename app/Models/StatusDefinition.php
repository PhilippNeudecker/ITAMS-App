<?php
namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StatusDefinition extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'business_code', 'module', 'name', 'description', 'sort_order', 'color', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
