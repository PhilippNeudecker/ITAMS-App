<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Manufacturer extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'business_code', 'name', 'website', 'support_contact',
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
