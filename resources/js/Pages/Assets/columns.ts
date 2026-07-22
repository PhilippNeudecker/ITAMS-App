import { h } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'
import { ArrowUpDown } from '@lucide/vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import DropdownAction from '@/components/data-table/DataTableDropDown.vue'
import { formatDate, formatDateShort } from '@/lib/utils'

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
            accessorKey: 'asset_label',
            header: () => 'Label',
            cell: ({ row }) => h('div', { class: '' }, row.getValue('asset_label')),
            meta: { headerClass: '', cellClass: '' },
        },
        {
            id: 'actions',
            enableHiding: false,
            header: () => h('div', { class: 'sr-only' }, 'Aktionen'),
            cell: ({ row }) => h('div', { class: 'relative w-fit' }, h(DropdownAction, {
                data: row.original,
                onView: handlers.onView,
                onEdit: handlers.onEdit,
                onCopy: handlers.onCopy,
                onDelete: handlers.onDelete,
            })),
            meta: { headerClass: 'w-px whitespace-nowrap', cellClass: 'w-px whitespace-nowrap' },
        },
        {
            accessorKey: 'name',
            header: ({ column }) => {
                return h(Button, {
                    class: 'font-medium',
                    variant: 'ghost',
                    onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
                }, () => ['Name', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })])
            },
            cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('name')),
            meta: { headerClass: 'w-fit', cellClass: 'w-fit' },
        },
        {
            accessorKey: 'category',
            header: () => h('div', { class: 'font-medium' }, 'Kategorie'),
            cell: ({ row }) => {
                const category = row.original.category
                if (!category) return h('span', { class: 'text-muted-foreground' }, '—')

                return h(Badge, {
                    variant: 'outline',
                    style: category.color ? `border-color: ${category.color}40; color: ${category.color}` : ''
                }, () => category.name)
            },
            meta: { headerClass: 'w-fit', cellClass: 'w-fit' },
        },
        {
            accessorKey: 'status',
            header: () => h('div', { class: 'font-medium' }, 'Status'),
            cell: ({ row }) => {
                const status = row.original.status
                if (!status) return h('span', { class: 'text-muted-foreground' }, '—')

                return h(Badge, {
                    variant: 'outline',
                    style: status.color ? `border-color: ${status.color}40; color: ${status.color}` : ''
                }, () => status.name)
            },
            meta: { headerClass: 'w-fit', cellClass: 'w-fit' },
        },
        {
            accessorKey: 'location',
            header: () => h('div', { class: 'font-medium' }, 'Standort'),
            cell: ({ row }) => {
                const locationName = row.original.location?.name
                return h('div', { class: 'text-sm' }, locationName ?? '—')
            },
            meta: { headerClass: 'w-fit', cellClass: 'w-fit' },
        },
        {
            accessorKey: 'manufacturer',
            header: () => h('div', { class: 'font-medium' }, 'Hersteller'),
            cell: ({ row }) => {
                const manufacturerName = row.original.manufacturer?.name
                return h('div', { class: 'text-sm' }, manufacturerName ?? '—')
            },
            meta: { headerClass: 'w-fit', cellClass: 'w-fit' },
        },
        {
            accessorKey: 'assigned_to',
            header: () => h('div', { class: 'font-medium' }, 'Zugewiesen an'),
            cell: ({ row }) => {
                const displayName = row.original.active_assignment?.employee?.display_name
                return h('div', { class: 'text-sm' }, displayName ?? '—')
            },
            meta: { headerClass: 'w-fit', cellClass: 'w-fit' },
        },
        {
            accessorKey: 'warranty_end_date',
            header: () => h('div', { class: 'font-medium' }, 'Garantieende'),
            cell: ({ row }) => {
                const date = row.getValue('warranty_end_date')
                return h(Badge, {
                    variant: 'destructive'//warrantyBadgeVariant(date)
                }, () => formatDate(date))
            },
            meta: { headerClass: 'w-fit', cellClass: 'w-fit' },
        }
    ]
}
