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
    private string $employeeIdKey;

    public function __construct()
    {
        $this->host           = config('ldap.host',              env('LDAP_HOST', 'localhost'));
        $this->port           = (int) config('ldap.port',        env('LDAP_PORT', 389));
        $this->baseDn         = config('ldap.base_dn',           env('LDAP_BASE_DN', ''));
        $this->bindDn         = config('ldap.bind_dn',           env('LDAP_BIND_DN', ''));
        $this->bindPassword   = config('ldap.bind_password',     env('LDAP_BIND_PASSWORD', ''));
        $this->userFilter     = config('ldap.user_filter',       env('LDAP_USER_FILTER', '(sAMAccountName=%s)'));
        $this->employeeIdAttr = config('ldap.employee_id_attr',  env('LDAP_EMPLOYEE_ID_ATTR', 'employeeid'));
        $this->employeeIdKey  = strtolower($this->employeeIdAttr);
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
     * Create or update the local Employee record from a single LDAP entry
     * (used by authenticate()-driven, single-user sync).
     */
    public function syncEmployee(array $entry): Employee
    {
        $employeeId = $entry[$this->employeeIdKey][0] ?? null;

        if (!$employeeId) {
            throw new \RuntimeException('LDAP user has no employeeID attribute configured.');
        }

        return Employee::updateOrCreate(
            ['employee_id' => $employeeId],
            [
                // 'business_code'      => config('itams.business_code', 'DEFAULT'),
                'upn'                => $entry['mail'][0] ?? null,
                'mail'               => $entry['mail'][0] ?? null,
                'first_name'         => $entry['givenname'][0] ?? '',
                'last_name'          => $entry['sn'][0] ?? '',
                'display_name'       => $entry['displayname'][0] ?? null,
                'ad_status'          => 'active',
                'last_sync_at'       => now(),
            ]
        );
    }

    /**
     * Bulk-sync all "person" accounts from AD into the local employees table.
     * - Creates new employees, updates existing ones.
     * - Employees no longer returned by AD (or disabled there) are set to 'inactive'.
     * - Only real user/person objects are pulled (computers, groups, contacts excluded).
     *
     * @return array{created:int, updated:int, deactivated:int, skipped:int, errors:array<int,string>}
     */
    public function syncAllEmployees(int $pageSize = 500): array
    {
        $stats = [
            'created'     => 0,
            'updated'     => 0,
            'deactivated' => 0,
            'skipped'     => 0,
            'errors'      => [],
        ];

        $conn = @ldap_connect("ldap://{$this->host}:{$this->port}");
        if (!$conn) {
            throw new \RuntimeException("Konnte keine Verbindung zu LDAP-Server {$this->host} herstellen.");
        }

        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

        if (!@ldap_bind($conn, $this->bindDn, $this->bindPassword)) {
            throw new \RuntimeException('LDAP Service-Bind fehlgeschlagen.');
        }

        // Nur echte Personen-Konten, keine Computer/Gruppen/Kontakte.
        $filter = '(&(objectCategory=person)(objectClass=user))';

        $attributes = [
            'dn', 'sAMAccountName', 'mail', 'givenName', 'sn', 'displayName',
            'userAccountControl', 'objectGUID', $this->employeeIdAttr,
        ];

        $syncStartedAt = now();
        $cookie = '';

        do {
            $controls = [
                [
                    'oid'        => LDAP_CONTROL_PAGEDRESULTS,
                    'iscritical' => true,
                    'value'      => ['size' => $pageSize, 'cookie' => $cookie],
                ],
            ];

            $result = @ldap_search($conn, $this->baseDn, $filter, $attributes, 0, 0, 0, LDAP_DEREF_NEVER, $controls);

            if (!$result) {
                $stats['errors'][] = 'LDAP-Suche fehlgeschlagen: ' . ldap_error($conn);
                break;
            }

            $entries = ldap_get_entries($conn, $result);
            unset($entries['count']);

            foreach ($entries as $entry) {
                try {
                    $outcome = $this->upsertFromEntry($entry);
                    $stats[$outcome]++;
                } catch (\Throwable $e) {
                    $label = $entry[$this->employeeIdKey][0] ?? $entry['dn'] ?? 'unbekannt';
                    $stats['errors'][] = "{$label}: {$e->getMessage()}";
                }
            }

            ldap_parse_result($conn, $result, $errcode, $matcheddn, $errmsg, $referrals, $controls);
            $cookie = $controls[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'] ?? '';
        } while (!empty($cookie));

        ldap_unbind($conn);

        // Mitarbeiter, die im AD nicht mehr auftauchen, als inaktiv markieren
        $stats['deactivated'] = Employee::where('ad_status', 'active')
            ->where(function ($q) use ($syncStartedAt) {
                $q->whereNull('last_sync_at')
                  ->orWhere('last_sync_at', '<', $syncStartedAt);
            })
            ->update(['ad_status' => 'inactive']);

        return $stats;
    }

    private function upsertFromEntry(array $entry): string
    {
        $employeeId = $entry[$this->employeeIdKey][0] ?? null;

        if (!$employeeId) {
            return 'skipped';
        }

        $uac        = isset($entry['useraccountcontrol'][0]) ? (int) $entry['useraccountcontrol'][0] : 0;
        $isDisabled = ($uac & 2) === 2; // ACCOUNTDISABLE bit

        $existed = Employee::where('employee_id', $employeeId)->exists();

        Employee::updateOrCreate(
            ['employee_id' => $employeeId],
            [
                'external_object_id' => isset($entry['objectguid'][0]) ? $this->formatGuid($entry['objectguid'][0]) : null,
                'upn'                => $entry['mail'][0] ?? null,
                'mail'               => $entry['mail'][0] ?? null,
                'first_name'         => $entry['givenname'][0] ?? '',
                'last_name'          => $entry['sn'][0] ?? '',
                'display_name'       => $entry['displayname'][0] ?? null,
                'ad_status'          => $isDisabled ? 'inactive' : 'active',
                'last_sync_at'       => now(),
            ]
        );

        return $existed ? 'updated' : 'created';
    }

    /** Converts a binary objectGUID into the standard string GUID format. */
    private function formatGuid(string $binaryGuid): string
    {
        $hex = bin2hex($binaryGuid);

        return sprintf(
            '%s%s%s%s-%s%s-%s%s-%s-%s',
            substr($hex, 6, 2), substr($hex, 4, 2), substr($hex, 2, 2), substr($hex, 0, 2),
            substr($hex, 10, 2), substr($hex, 8, 2),
            substr($hex, 14, 2), substr($hex, 12, 2),
            substr($hex, 16, 4),
            substr($hex, 20, 12)
        );
    }
}
