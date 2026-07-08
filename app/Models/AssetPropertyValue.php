<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetPropertyValue extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'business_code', 'asset_id', 'property_definition_id',
        'value_text', 'value_number', 'value_date', 'value_bool', 'property_option_id',
    ];

    protected $casts = [
        'value_number' => 'decimal:4',
        'value_date'   => 'date',
        'value_bool'   => 'boolean',
    ];

    public function asset()       { return $this->belongsTo(Asset::class); }
    public function definition()  { return $this->belongsTo(PropertyDefinition::class, 'property_definition_id'); }
    public function option()      { return $this->belongsTo(PropertyOption::class, 'property_option_id'); }

    /**
     * Returns the typed value based on the definition's data_type.
     */
    public function getTypedValueAttribute(): mixed
    {
        return match ($this->definition?->data_type) {
            PropertyDefinition::TYPE_NUMBER  => $this->value_number,
            PropertyDefinition::TYPE_DATE    => $this->value_date,
            PropertyDefinition::TYPE_BOOLEAN => $this->value_bool,
            PropertyDefinition::TYPE_OPTION  => $this->option?->option_value,
            default                          => $this->value_text,
        };
    }
}
