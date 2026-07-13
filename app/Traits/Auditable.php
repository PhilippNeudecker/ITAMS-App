<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Replaces EntityBase + AuditableEntityBase from C#.
 *
 * Every model using this trait gets:
 *  - UUID primary key (auto-generated on create)
 *  - business_code (single-tenant: set once via config or middleware)
 *  - created_by_employee_id / updated_by_employee_id
 *  - deleted_by_employee_id  (compatible with SoftDeletes)
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->business_code)) {
                $model->business_code = config('itams.business_code', 'DEFAULT');
            }
            if (empty($model->created_by_employee_id)) {
                $model->created_by_employee_id = self::currentEmployeeId();
            }
        });

        static::updating(function ($model) {
            $model->updated_by_employee_id = self::currentEmployeeId();
        });

        static::deleting(function ($model) {
            if (method_exists($model, 'runSoftDelete')) {
                // SoftDelete path
                $model->deleted_by_employee_id = self::currentEmployeeId();
                $model->saveQuietly();
            }
        });
    }

    private static function currentEmployeeId(): string
    {
        // Auth user has employee_id set after LDAP login (see LdapAuthService)
        $user = auth()->user();
        return $user?->employee_id ?? 'ADMIN';
    }

    /**
     * The primary key is a UUID string, not an auto-increment integer.
     */
    public function initializeAuditable(): void
    {
        $this->keyType    = 'string';
        $this->incrementing = false;
    }
}
