import { h } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'
import { CheckIcon, XIcon } from 'lucide-vue-next'
import { Checkbox } from '@/components/ui/checkbox'
import { Badge } from '@/components/ui/badge'
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
            accessorKey: 'name',
            header: () => 'Name',
            cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('name')),
            meta: { headerClass: 'w-fit', cellClass: 'w-fit' },
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
            accessorKey: 'website',
            header: () => 'Website',
            cell: ({ row }) => h('div', { class: 'text-muted-foreground' }, row.getValue('website') || '—'),
            meta: { headerClass: 'w-fit', cellClass: 'w-fit' },
        },
        {
            accessorKey: 'support_contact',
            header: () => 'Support Mail',
            cell: ({ row }) => h('div', { class: 'text-muted-foreground' }, row.getValue('support_contact') || '—'),
            meta: { headerClass: 'w-fit', cellClass: 'w-fit' },
        },
    ]
}
