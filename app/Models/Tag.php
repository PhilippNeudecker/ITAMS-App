<?php namespace App\Models;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use Auditable, SoftDeletes;
    protected $fillable = ['business_code','name','description','color','is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function assets()
    {
        return $this->belongsToMany(Asset::class, 'asset_tag')
                    ->withPivot(['id','created_by_employee_id'])
                    ->withTimestamps();
    }
}
