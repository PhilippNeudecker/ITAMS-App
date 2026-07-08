import { resolveComponent, h } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'
import { CheckIcon, XIcon } from 'lucide-vue-next';

// interface Payment {
//   id: string
//   amount: number
//   status: 'pending' | 'processing' | 'success' | 'failed'
//   email: string
// }

export const data: any[] = [
    {
        "business_code": "IT",
        "name": "Notebook",
        "description": "Notebook Computer",
        "color": "#2563EB",
        "is_active": true
    },
    {
        "business_code": "IT",
        "name": "Desktop",
        "description": "Desktop PC",
        "color": "#1D4ED8",
        "is_active": true
    },
    {
        "business_code": "IT",
        "name": "Monitor",
        "description": "Bildschirm",
        "color": "#3B82F6",
        "is_active": true
    },
    {
        "business_code": "IT",
        "name": "Drucker",
        "description": "Druck- und Scangerät",
        "color": "#06B6D4",
        "is_active": true
    },
    {
        "business_code": "IT",
        "name": "Smartphone",
        "description": "Mobiltelefon",
        "color": "#0891B2",
        "is_active": true
    },
    {
        "business_code": "IT",
        "name": "Tablet",
        "description": "Tablet Gerät",
        "color": "#0EA5E9",
        "is_active": true
    },
    {
        "business_code": "IT",
        "name": "Server",
        "description": "Physischer Server",
        "color": "#DC2626",
        "is_active": true
    },
    {
        "business_code": "IT",
        "name": "Virtuelle Maschine",
        "description": "Virtuelle Serverinstanz",
        "color": "#B91C1C",
        "is_active": false
    },
    {
        "business_code": "NET",
        "name": "Switch",
        "description": "Netzwerkswitch",
        "color": "#EA580C",
        "is_active": true
    },
    {
        "business_code": "NET",
        "name": "Firewall",
        "description": "Firewall System",
        "color": "#C2410C",
        "is_active": true
    },
    {
        "business_code": "NET",
        "name": "Router",
        "description": "Netzwerkrouter",
        "color": "#FB923C",
        "is_active": true
    },
    {
        "business_code": "NET",
        "name": "Access Point",
        "description": "WLAN Access Point",
        "color": "#F97316",
        "is_active": true
    },
    {
        "business_code": "SW",
        "name": "Microsoft",
        "description": "Microsoft Software",
        "color": "#7C3AED",
        "is_active": true
    },
    {
        "business_code": "SW",
        "name": "Adobe",
        "description": "Adobe Software",
        "color": "#9333EA",
        "is_active": true
    },
    {
        "business_code": "SW",
        "name": "ERP",
        "description": "ERP Software",
        "color": "#8B5CF6",
        "is_active": true
    },
    {
        "business_code": "SW",
        "name": "DMS",
        "description": "Dokumentenmanagement",
        "color": "#A855F7",
        "is_active": true
    },
    {
        "business_code": "USR",
        "name": "Geschäftsleitung",
        "description": "Zur Geschäftsleitung zugeordnet",
        "color": "#16A34A",
        "is_active": true
    },
    {
        "business_code": "USR",
        "name": "Vertrieb",
        "description": "Asset Vertrieb",
        "color": "#22C55E",
        "is_active": true
    },
    {
        "business_code": "USR",
        "name": "Produktion",
        "description": "Asset Produktion",
        "color": "#4ADE80",
        "is_active": true
    },
    {
        "business_code": "USR",
        "name": "Logistik",
        "description": "Asset Logistik",
        "color": "#86EFAC",
        "is_active": true
    },
    {
        "business_code": "USR",
        "name": "IT",
        "description": "Asset IT-Abteilung",
        "color": "#15803D",
        "is_active": true
    },
    {
        "business_code": "SEC",
        "name": "Verschlüsselt",
        "description": "Gerät verschlüsselt",
        "color": "#E11D48",
        "is_active": true
    },
    {
        "business_code": "SEC",
        "name": "MFA Aktiv",
        "description": "Mehrfaktor Authentifizierung aktiviert",
        "color": "#F43F5E",
        "is_active": true
    },
    {
        "business_code": "SEC",
        "name": "Compliance",
        "description": "Compliance geprüft",
        "color": "#FB7185",
        "is_active": true
    },
    {
        "business_code": "LCM",
        "name": "Neuanschaffung",
        "description": "Neu beschafftes Asset",
        "color": "#0F766E",
        "is_active": true
    },
    {
        "business_code": "LCM",
        "name": "In Betrieb",
        "description": "Produktiv im Einsatz",
        "color": "#14B8A6",
        "is_active": true
    },
    {
        "business_code": "LCM",
        "name": "Reserve",
        "description": "Reservegerät",
        "color": "#2DD4BF",
        "is_active": true
    },
    {
        "business_code": "LCM",
        "name": "Defekt",
        "description": "Defektes Asset",
        "color": "#EF4444",
        "is_active": true
    },
    {
        "business_code": "LCM",
        "name": "Ausgemustert",
        "description": "Stillgelegtes Asset",
        "color": "#6B7280",
        "is_active": true
    },
    {
        "business_code": "LCM",
        "name": "Leasing",
        "description": "Asset im Leasing",
        "color": "#374151",
        "is_active": true
    }
]

export const columns: ColumnDef<any>[] = [
    {
        accessorKey: 'business_code',
        header: () => h('div', { class: 'font-medium' }, 'Businesscode'),
        cell: ({ row }) => {

            return h('div', {}, row.getValue('business_code'))
        },
    },
    {
        accessorKey: 'name',
        header: () => h('div', { class: 'font-medium' }, 'Name'),
        cell: ({ row }) => {
            return h('div', {}, row.getValue('name'))
        },
    },
    {
        accessorKey: 'description',
        header: () => h('div', { class: 'font-medium' }, 'Beschreibung'),
        cell: ({ row }) => {
            return h('div', {}, row.getValue('description'))
        },
    },
    {
        accessorKey: 'color',
        header: () => h('div', { class: 'font-medium text-primary' }, 'Farbe'),
        cell: ({ row }) => {
            return h('div', {}, row.getValue('color'))
        },
    },
    {
        accessorKey: 'is_active',
        header: () => h('div', { class: 'font-medium text-primary' }, 'Aktiv'),
        cell: ({ row }) => {
            const isActive = row.getValue('is_active');
            return h(isActive ? CheckIcon : XIcon, {
                class: isActive ? 'justify-self-center text-green-600 h-5 w-5' : 'justify-self-center text-red-600 h-5 w-5',
            });
        },
    }
]
