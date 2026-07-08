<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'business_code', 'name', 'description',
        'parent_location_id', 'location_type_definition_id',
        'street', 'house_number', 'postal_code', 'city', 'country', 'additional_info',
    ];

    public function parent()
    {
        return $this->belongsTo(Location::class, 'parent_location_id');
    }

    public function children()
    {
        return $this->hasMany(Location::class, 'parent_location_id');
    }

    public function locationType()
    {
        return $this->belongsTo(LocationTypeDefinition::class, 'location_type_definition_id');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'current_location_id');
    }

    /** Full address as single string */
    public function getFullAddressAttribute(): ?string
    {
        $parts = array_filter([
            trim("{$this->street} {$this->house_number}"),
            trim("{$this->postal_code} {$this->city}"),
            $this->country,
        ]);
        return $parts ? implode(', ', $parts) : null;
    }
}
