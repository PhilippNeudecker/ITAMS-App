<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetTransaction extends Model
{
    use Auditable, SoftDeletes;

    const TYPE_ISSUE  = 'Issue';
    const TYPE_RETURN = 'Return';

    protected $fillable = [
        'business_code', 'asset_id', 'transaction_type', 'transaction_date',
        'performed_by_employee_id', 'counterparty_employee_id',
        'counterparty_display_name_snapshot', 'counterparty_mail_snapshot',
        'counterparty_cost_center_code_snapshot',
        'location_id', 'signature_document_storage_key', 'signed_at', 'comment',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'signed_at'        => 'datetime',
    ];

    public function asset()    { return $this->belongsTo(Asset::class); }
    public function location() { return $this->belongsTo(Location::class); }
}


// ── Attachment ───────────────────────────────────────────────────────────────

class Attachment extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'business_code', 'entity_name', 'entity_id',
        'storage_key', 'dms_guid', 'file_name', 'mime_type',
        'uploaded_at', 'uploaded_by_employee_id',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'dms_guid'    => 'string',
    ];
}


// ── StatusHistory ────────────────────────────────────────────────────────────

class StatusHistory extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'business_code', 'entity_name', 'entity_id',
        'status_definition_id', 'valid_from', 'valid_to',
        'changed_at', 'changed_by_employee_id', 'comment',
    ];

    protected $casts = [
        'valid_from'  => 'datetime',
        'valid_to'    => 'datetime',
        'changed_at'  => 'datetime',
    ];

    public function statusDefinition()
    {
        return $this->belongsTo(StatusDefinition::class);
    }
}
