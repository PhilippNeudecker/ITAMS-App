<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategorySequence extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'business_code', 'category_id', 'next_sequence_number',
    ];

    protected $casts = [
        'next_sequence_number' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
