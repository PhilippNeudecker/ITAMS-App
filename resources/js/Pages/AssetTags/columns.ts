import { h } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'
import { CheckIcon, XIcon } from 'lucide-vue-next'
import { Checkbox } from '@/components/ui/checkbox'
import { Badge } from '@/components/ui/badge'
import DropdownAction from '@/Pages/AssetTags/DataTableDropDown.vue'

// export const data: any[] = [
//     {
//         "business_code": "IT",
//         "name": "Notebook",
//         "description": "Notebook Computer",
//         "color": "#2563EB",
//         "is_active": true
//     },
//     {
//         "business_code": "IT",
//         "name": "Desktop",
//         "description": "Desktop PC",
//         "color": "#1D4ED8",
//         "is_active": true
//     },
//     {
//         "business_code": "IT",
//         "name": "Monitor",
//         "description": "Bildschirm",
//         "color": "#3B82F6",
//         "is_active": false
//     },
    // {
    //     "business_code": "IT",
    //     "name": "Drucker",
    //     "description": "Druck- und Scangerät",
    //     "color": "#06B6D4",
    //     "is_active": true
    // },
    // {
    //     "business_code": "IT",
    //     "name": "Smartphone",
    //     "description": "Mobiltelefon",
    //     "color": "#0891B2",
    //     "is_active": true
    // },
    // {
    //     "business_code": "IT",
    //     "name": "Tablet",
    //     "description": "Tablet Gerät",
    //     "color": "#0EA5E9",
    //     "is_active": true
    // },
    // {
    //     "business_code": "IT",
    //     "name": "Server",
    //     "description": "Physischer Server",
    //     "color": "#DC2626",
    //     "is_active": true
    // },
    // {
    //     "business_code": "IT",
    //     "name": "Virtuelle Maschine",
    //     "description": "Virtuelle Serverinstanz",
    //     "color": "#B91C1C",
    //     "is_active": false
    // },
    // {
    //     "business_code": "NET",
    //     "name": "Switch",
    //     "description": "Netzwerkswitch",
    //     "color": "#EA580C",
    //     "is_active": true
    // },
    // {
    //     "business_code": "NET",
    //     "name": "Firewall",
    //     "description": "Firewall System",
    //     "color": "#C2410C",
    //     "is_active": true
    // },
    // {
    //     "business_code": "NET",
    //     "name": "Router",
    //     "description": "Netzwerkrouter",
    //     "color": "#FB923C",
    //     "is_active": true
    // },
    // {
    //     "business_code": "NET",
    //     "name": "Access Point",
    //     "description": "WLAN Access Point",
    //     "color": "#F97316",
    //     "is_active": true
    // },
    // {
    //     "business_code": "SW",
    //     "name": "Microsoft",
    //     "description": "Microsoft Software",
    //     "color": "#7C3AED",
    //     "is_active": true
    // },
    // {
    //     "business_code": "SW",
    //     "name": "Adobe",
    //     "description": "Adobe Software",
    //     "color": "#9333EA",
    //     "is_active": true
    // },
    // {
    //     "business_code": "SW",
    //     "name": "ERP",
    //     "description": "ERP Software",
    //     "color": "#8B5CF6",
    //     "is_active": true
    // },
    // {
    //     "business_code": "SW",
    //     "name": "DMS",
    //     "description": "Dokumentenmanagement",
    //     "color": "#A855F7",
    //     "is_active": true
    // },
    // {
    //     "business_code": "USR",
    //     "name": "Geschäftsleitung",
    //     "description": "Zur Geschäftsleitung zugeordnet",
    //     "color": "#16A34A",
    //     "is_active": true
    // },
    // {
    //     "business_code": "USR",
    //     "name": "Vertrieb",
    //     "description": "Asset Vertrieb",
    //     "color": "#22C55E",
    //     "is_active": true
    // },
    // {
    //     "business_code": "USR",
    //     "name": "Produktion",
    //     "description": "Asset Produktion",
    //     "color": "#4ADE80",
    //     "is_active": true
    // },
    // {
    //     "business_code": "USR",
    //     "name": "Logistik",
    //     "description": "Asset Logistik",
    //     "color": "#86EFAC",
    //     "is_active": true
    // },
    // {
    //     "business_code": "USR",
    //     "name": "IT",
    //     "description": "Asset IT-Abteilung",
    //     "color": "#15803D",
    //     "is_active": true
    // },
    // {
    //     "business_code": "SEC",
    //     "name": "Verschlüsselt",
    //     "description": "Gerät verschlüsselt",
    //     "color": "#E11D48",
    //     "is_active": true
    // },
    // {
    //     "business_code": "SEC",
    //     "name": "MFA Aktiv",
    //     "description": "Mehrfaktor Authentifizierung aktiviert",
    //     "color": "#F43F5E",
    //     "is_active": true
    // },
    // {
    //     "business_code": "SEC",
    //     "name": "Compliance",
    //     "description": "Compliance geprüft",
    //     "color": "#FB7185",
    //     "is_active": true
    // },
    // {
    //     "business_code": "LCM",
    //     "name": "Neuanschaffung",
    //     "description": "Neu beschafftes Asset",
    //     "color": "#0F766E",
    //     "is_active": true
    // },
    // {
    //     "business_code": "LCM",
    //     "name": "In Betrieb",
    //     "description": "Produktiv im Einsatz",
    //     "color": "#14B8A6",
    //     "is_active": true
    // },
    // {
    //     "business_code": "LCM",
    //     "name": "Reserve",
    //     "description": "Reservegerät",
    //     "color": "#2DD4BF",
    //     "is_active": true
    // },
    // {
    //     "business_code": "LCM",
    //     "name": "Defekt",
    //     "description": "Defektes Asset",
    //     "color": "#EF4444",
    //     "is_active": true
    // },
    // {
    //     "business_code": "LCM",
    //     "name": "Ausgemustert",
    //     "description": "Stillgelegtes Asset",
    //     "color": "#6B7280",
    //     "is_active": true
    // },
    // {
    //     "business_code": "LCM",
    //     "name": "Leasing",
    //     "description": "Asset im Leasing",
    //     "color": "#374151",
    //     "is_active": true
    // }
// ]

export function buildColumns(handlers: {
    isSelected: (id: number) => boolean
    toggleSelected: (id: number, value: boolean) => void
    isAllSelected: () => boolean | 'indeterminate'
    toggleAll: (value: boolean) => void
    onView: (id: number) => void
    onEdit: (id: number) => void
    onCopy: (id: number) => void
    onDelete: (id: number) => void
}): ColumnDef<any>[] {
    return [
        {
            id: 'select',
            header: () => h(Checkbox, {
                'modelValue': handlers.isAllSelected(),
                'onUpdate:modelValue': (value: any) => handlers.toggleAll(!!value),
                'ariaLabel': 'Alle auswählen',
            }),
            cell: ({ row }) => h('div', { onClick: (e: Event) => e.stopPropagation() }, [
                h(Checkbox, {
                    'modelValue': handlers.isSelected(row.original.id),
                    'onUpdate:modelValue': (value: any) => handlers.toggleSelected(row.original.id, !!value),
                    'ariaLabel': 'Zeile auswählen',
                }),
            ]),
            enableSorting: false,
            enableHiding: false,
            meta: { headerClass: 'w-px whitespace-nowrap', cellClass: 'w-px whitespace-nowrap' },
        },
        {
            accessorKey: 'business_code',
            header: 'Businesscode',
            cell: ({ row }) => h('div', {}, row.getValue('business_code')),
            meta: { headerClass: 'w-px whitespace-nowrap', cellClass: 'w-px whitespace-nowrap' },
        },
        {
            id: 'actions',
            enableHiding: false,
            header: () => h('div', { class: 'sr-only' }, 'Aktionen'),
            cell: ({ row }) => h('div', { class: 'relative w-fit' }, h(DropdownAction, {
                tag: row.original,
                onView: handlers.onView,
                onEdit: handlers.onEdit,
                onCopy: handlers.onCopy,
                onDelete: handlers.onDelete,
            })),
            meta: { headerClass: 'w-px whitespace-nowrap', cellClass: 'w-px whitespace-nowrap' },
        },
        {
            accessorKey: 'name',
            header: () => 'Name',
            cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('name')),
            meta: { headerClass: 'w-[50%]', cellClass: 'w-[50%]' },
        },
        {
            accessorKey: 'description',
            header: () => 'Beschreibung',
            cell: ({ row }) => h('div', { class: 'text-muted-foreground' }, row.getValue('description') || '—'),
            meta: { headerClass: 'w-[50%]', cellClass: 'w-[50%]' },
        },
        {
            accessorKey: 'color',
            header: () => 'Farbe',
            cell: ({ row }) => {
                const color = row.getValue('color') as string | null
                return h('div', {}, h(Badge, {
                    class: 'rounded-l',
                    style: color ? `background-color:${color}20; color:${color}; border-color:${color}40` : '',
                }, () => color ?? '—'))
            },
            meta: { headerClass: 'w-px whitespace-nowrap', cellClass: 'w-px whitespace-nowrap' },
        },
        {
            accessorKey: 'is_active',
            header: () => 'Aktiv',
            cell: ({ row }) => {
                const isActive = row.getValue('is_active')
                return h(isActive ? CheckIcon : XIcon, {
                    class: isActive ? 'justify-self-center text-green-600 h-5 w-5' : 'justify-self-center text-red-600 h-5 w-5',
                })
            },
            meta: { headerClass: 'w-px whitespace-nowrap text-center', cellClass: 'w-px whitespace-nowrap text-center' },
        },
    ]
}
