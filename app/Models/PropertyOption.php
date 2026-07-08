<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyOption extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'business_code', 'property_definition_id',
        'option_value', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function definition()
    {
        return $this->belongsTo(PropertyDefinition::class, 'property_definition_id');
    }
}
