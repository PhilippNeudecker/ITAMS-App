<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'business_code',
        'asset_label', 'asset_sequence_number',
        'category_id', 'status_definition_id',
        'name', 'description',
        'manufacturer_id',
        'supplier_number', 'supplier_name', 'supplier_address',
        'supplier_post_code', 'supplier_city', 'supplier_country',
        'current_location_id',
        'image_storage_key', 'barcode_value', 'qr_value',
        'purchase_value', 'purchase_date', 'purchase_document_guid',
        'warranty_start_date', 'warranty_end_date', 'warranty_notify_days_before',
    ];

    protected $casts = [
        'purchase_value'               => 'decimal:2',
        'purchase_date'                => 'date',
        'warranty_start_date'          => 'date',
        'warranty_end_date'            => 'date',
        'warranty_notify_days_before'  => 'integer',
        'asset_sequence_number'        => 'integer',
    ];

    // ── Relations ────────────────────────────────

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function status()
    {
        return $this->belongsTo(StatusDefinition::class, 'status_definition_id');
    }

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'current_location_id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'asset_tag')
                    ->withPivot(['id', 'created_by_employee_id'])
                    ->withTimestamps();
    }

    public function propertyValues()
    {
        return $this->hasMany(AssetPropertyValue::class);
    }

    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class)->latest('assigned_from');
    }

    public function activeAssignment()
    {
        return $this->hasOne(AssetAssignment::class)->whereNull('assigned_to');
    }

    public function transactions()
    {
        return $this->hasMany(AssetTransaction::class)->latest('transaction_date');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'entity_id')
                    ->where('entity_name', 'Asset');
    }

    public function statusHistory()
    {
        return $this->hasMany(StatusHistory::class, 'entity_id')
                    ->where('entity_name', 'Asset')
                    ->latest('valid_from');
    }

    // ── Computed ─────────────────────────────────

    public function isWarrantyActive(): bool
    {
        if (!$this->warranty_end_date) return false;
        return $this->warranty_end_date->isFuture();
    }

    public function warrantyExpiresInDays(): ?int
    {
        if (!$this->warranty_end_date) return null;
        return (int) now()->startOfDay()->diffInDays($this->warranty_end_date, false);
    }
}
