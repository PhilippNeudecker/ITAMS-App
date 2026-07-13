import { h } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'
import { CheckIcon, XIcon } from 'lucide-vue-next'
import { Checkbox } from '@/components/ui/checkbox'
import { Badge } from '@/components/ui/badge'
import DropdownAction from '@/Pages/AssetCategories/DataTableDropDown.vue';

export const data: any[] = [
    {
        'id': 1,
        'business_code': 'IT',
        'name': 'Hardware',
        'description': 'Alle Hardware Assets',
        'color': '#2563EB',
        'parent_category_id': '1',
        'asset_prefix': 'HW',
        'asset_separator': '-',
        'asset_number_length': '6',
        'default_warranty_days': '365',
        'default_warranty_notify_days_before': '30'
    },
    {
        'id': 2,
        'business_code': 'IT',
        'name': 'Laptop',
        'description': 'Alle Laptop Assets',
        'color': '#2563EB',
        'parent_category_id': '1',
        'asset_prefix': 'NB',
        'asset_separator': '-',
        'asset_number_length': '6',
        'default_warranty_days': '365',
        'default_warranty_notify_days_before': '30'
    },
    {
        'id': 3,
        'business_code': 'IT',
        'name': 'Software',
        'description': 'Alle Software Assets',
        'color': '#2563EB',
        'parent_category_id': '1',
        'asset_prefix': 'SW',
        'asset_separator': '-',
        'asset_number_length': '6',
        'default_warranty_days': '365',
        'default_warranty_notify_days_before': '30'
    },
]

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
                category: row.original,
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
            meta: { headerClass: '', cellClass: '' },
        },
        {
            accessorKey: 'description',
            header: () => 'Beschreibung',
            cell: ({ row }) => h('div', { class: 'text-muted-foreground' }, row.getValue('description') || '—'),
            meta: { headerClass: '', cellClass: '' },
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
            accessorKey: 'asset_prefix',
            header: () => 'Präfix',
            cell: ({ row }) => {
                return h('div', { class: '' }, row.getValue('asset_prefix'))
            },
            meta: { headerClass: 'w-px whitespace-nowrap text-center', cellClass: 'w-px whitespace-nowrap text-center' },
        },,
        {
            accessorKey: 'default_warranty_days',
            header: () => 'Std.-Garantietage',
            cell: ({ row }) => {
                return h('div', { class: '' }, row.getValue('default_warranty_days'))
            },
            meta: { headerClass: 'w-px whitespace-nowrap text-center', cellClass: 'w-px whitespace-nowrap text-center' },
        },
        {
            accessorKey: 'default_warranty_notify_days_before',
            header: () => 'Benachrichtigung',
            cell: ({ row }) => {
                return h('div', { class: '' }, row.getValue('default_warranty_notify_days_before'))
            },
            meta: { headerClass: 'w-px whitespace-nowrap text-center', cellClass: 'w-px whitespace-nowrap text-center' },
        }
    ]

}
