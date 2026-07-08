<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocationTypeDefinition extends Model
{
    use Auditable, SoftDeletes;
    protected $fillable = ['business_code','name','description','is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function locations() { return $this->hasMany(Location::class); }
}
