<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertyDefinition extends Model
{
    use Auditable, SoftDeletes;

    // data_type enum values
    const TYPE_TEXT    = 'Text';
    const TYPE_NUMBER  = 'Number';
    const TYPE_DATE    = 'Date';
    const TYPE_BOOLEAN = 'Boolean';
    const TYPE_OPTION  = 'Option';

    protected $fillable = [
        'business_code', 'scope', 'name', 'description',
        'data_type', 'unit', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function options()
    {
        return $this->hasMany(PropertyOption::class)->orderBy('sort_order');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_property_definitions')
                    ->withPivot([
                        'is_required', 'sort_order',
                        'default_value_text', 'default_value_number',
                        'default_value_date', 'default_value_bool',
                        'default_property_option_id',
                    ]);
    }
}
