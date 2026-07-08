<?php
// ═══════════════════════════════════════════════════
//  app/Models/  –  alle Asset-relevanten Modelle
//  Jedes Modell liegt normalerweise in einer eigenen
//  Datei; hier zusammengefasst für die Übersicht.
// ═══════════════════════════════════════════════════

// ── app/Models/CostCenter.php ────────────────────
namespace App\Models;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CostCenter extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'business_code', 'cost_center_code', 'name', 'description', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function employees() { return $this->hasMany(Employee::class); }
}
