import { h } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'
import { CheckIcon, XIcon } from 'lucide-vue-next'
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
            accessorKey: 'cost_center_code',
            header: () => 'Kostenstelle',
            cell: ({ row }) => h('div', { class: '' }, row.getValue('cost_center_code')),
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
            header: () => 'Name',
            cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('name')),
            meta: { headerClass: 'w-fit', cellClass: 'w-fit' },
        },
        {
            accessorKey: 'description',
            header: () => 'Beschreibung',
            cell: ({ row }) => h('div', { class: '' }, row.getValue('description') || '—'),
            meta: { headerClass: 'w-fit', cellClass: 'w-fit' },
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
