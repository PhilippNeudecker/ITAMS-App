<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'business_code', 'name', 'description', 'color',
        'parent_category_id',
        'asset_prefix', 'asset_separator', 'asset_number_length',
        'default_warranty_days', 'default_warranty_notify_days_before',
    ];

    protected $casts = [
        'asset_number_length'                   => 'integer',
        'default_warranty_days'                 => 'integer',
        'default_warranty_notify_days_before'   => 'integer',
    ];

    // ── Relations ────────────────────────────────

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_category_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_category_id');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function propertyDefinitions()
    {
        return $this->belongsToMany(
            PropertyDefinition::class,
            'category_property_definitions'
        )->withPivot([
            'is_required', 'sort_order',
            'default_value_text', 'default_value_number',
            'default_value_date', 'default_value_bool',
            'default_property_option_id',
        ])->orderByPivot('sort_order');
    }

    public function sequence()
    {
        return $this->hasOne(CategorySequence::class);
    }

    // ── Label Helper ────────────────────────────

    /**
     * Generates the next asset label, e.g. "LAP-000042".
     * Uses a DB-level lock to prevent race conditions.
     */
    public function generateNextLabel(): string
    {
        $seq = $this->sequence()->lockForUpdate()->firstOrCreate(
            ['category_id' => $this->id],
            ['next_sequence_number' => 1, 'business_code' => $this->business_code,
             'created_by_employee_id' => 'SYSTEM']
        );

        $number = $seq->next_sequence_number;
        $seq->increment('next_sequence_number');

        $padded = str_pad($number, $this->asset_number_length, '0', STR_PAD_LEFT);

        return "{$this->asset_prefix}{$this->asset_separator}{$padded}";
    }
}
