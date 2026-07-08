<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Facades\Log;

/**
 * LDAP authentication and employee sync.
 *
 * Requires php-ldap extension and config/ldap.php (or .env vars below).
 *
 * .env:
 *   LDAP_HOST=dc01.example.com
 *   LDAP_PORT=389
 *   LDAP_BASE_DN=DC=example,DC=com
 *   LDAP_BIND_DN=CN=svc-itams,OU=ServiceAccounts,DC=example,DC=com
 *   LDAP_BIND_PASSWORD=secret
 *   LDAP_USER_FILTER=(sAMAccountName=%s)
 *   LDAP_EMPLOYEE_ID_ATTR=employeeID      # 5-char personnel number
 *   LDAP_OBJECT_ID_ATTR=objectGuid
 */
class LdapAuthService
{
    private string $host;
    private int    $port;
    private string $baseDn;
    private string $bindDn;
    private string $bindPassword;
    private string $userFilter;
    private string $employeeIdAttr;

    public function __construct()
    {
        $this->host           = config('ldap.host',              env('LDAP_HOST', 'localhost'));
        $this->port           = (int) config('ldap.port',        env('LDAP_PORT', 389));
        $this->baseDn         = config('ldap.base_dn',           env('LDAP_BASE_DN', ''));
        $this->bindDn         = config('ldap.bind_dn',           env('LDAP_BIND_DN', ''));
        $this->bindPassword   = config('ldap.bind_password',     env('LDAP_BIND_PASSWORD', ''));
        $this->userFilter     = config('ldap.user_filter',       env('LDAP_USER_FILTER', '(sAMAccountName=%s)'));
        $this->employeeIdAttr = config('ldap.employee_id_attr',  env('LDAP_EMPLOYEE_ID_ATTR', 'employeeid'));
    }

    /**
     * Authenticate user via LDAP bind.
     * Returns the LDAP entry array on success, null on failure.
     */
    public function authenticate(string $username, string $password): ?array
    {
        if (empty($password)) return null;

        $conn = @ldap_connect("ldap://{$this->host}:{$this->port}");
        if (!$conn) return null;

        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

        // Service-bind to find the user DN
        if (!@ldap_bind($conn, $this->bindDn, $this->bindPassword)) {
            Log::warning('LDAP service bind failed');
            return null;
        }

        $filter = sprintf($this->userFilter, ldap_escape($username, '', LDAP_ESCAPE_FILTER));
        $result = ldap_search($conn, $this->baseDn, $filter, [
            'dn', 'sAMAccountName', 'mail', 'givenName', 'sn',
            'displayName', 'department', $this->employeeIdAttr, 'objectGuid',
        ]);

        if (!$result) return null;

        $entries = ldap_get_entries($conn, $result);
        if ($entries['count'] === 0) return null;

        $entry   = $entries[0];
        $userDn  = $entry['dn'];

        // User-bind with provided password
        if (!@ldap_bind($conn, $userDn, $password)) {
            return null;
        }

        ldap_unbind($conn);
        return $entry;
    }

    /**
     * Create or update the local Employee record from LDAP data.
     */
    public function syncEmployee(array $entry): Employee
    {
        $employeeId = $entry[$this->employeeIdAttr][0] ?? null;

        if (!$employeeId) {
            throw new \RuntimeException('LDAP user has no employeeID attribute configured.');
        }

        return Employee::updateOrCreate(
            ['employee_id' => $employeeId],
            [
                'business_code'      => config('itams.business_code', 'DEFAULT'),
                'upn'                => $entry['mail'][0] ?? null,
                'mail'               => $entry['mail'][0] ?? null,
                'first_name'         => $entry['givenname'][0] ?? '',
                'last_name'          => $entry['sn'][0] ?? '',
                'display_name'       => $entry['displayname'][0] ?? null,
                'ad_status'          => 'active',
                'last_sync_at'       => now(),
                // created_by / updated_by handled by Auditable trait
                'created_by_employee_id' => $employeeId,
                'updated_by_employee_id' => $employeeId,
            ]
        );
    }
}
