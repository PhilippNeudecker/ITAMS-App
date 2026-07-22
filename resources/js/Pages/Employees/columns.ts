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
            accessorKey: 'employee_id',
            header: () => 'Personalnr.',
            cell: ({ row }) => h('div', { class: '' }, row.getValue('employee_id')),
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
            accessorKey: 'first_name',
            header: () => 'Vorname',
            cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('first_name')),
            meta: { headerClass: 'w-fit', cellClass: 'w-fit' },
            enableSorting: true,
        },
        {
            accessorKey: 'last_name',
            header: () => 'Nachname',
            cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('last_name')),
            meta: { headerClass: 'w-fit', cellClass: 'w-fit' },
            enableSorting: true,
        },
        // {
        //     accessorKey: 'display_name',
        //     header: () => 'Name',
        //     cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('display_name')),
        //     meta: { headerClass: 'w-fit', cellClass: 'w-fit' },
        // },
        {
            accessorKey: 'mail',
            header: () => 'Mail',
            cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('mail')),
            meta: { headerClass: 'w-fit', cellClass: 'w-fit' },
        },
        {
            accessorKey: 'cost_center_id',
            header: () => 'KST',
            cell: ({ row }) => h('div', { class: '' }, row.getValue('cost_center_id') || '—'),
            meta: { headerClass: 'w-fit', cellClass: 'w-fit' },
        },
        {
            accessorKey: 'ad_status',
            header: () => 'AD Status',
            cell: ({ row }) => {
                const isActive = row.getValue('ad_status')
                return h(isActive ? CheckIcon : XIcon, {
                    class: isActive ? 'justify-self-center text-green-600 h-5 w-5' : 'justify-self-center text-red-600 h-5 w-5',
                })
            },
            meta: { headerClass: 'w-px whitespace-nowrap text-center', cellClass: 'w-px whitespace-nowrap text-center' },
        },
        {
            accessorKey: 'last_sync_at',
            header: () => 'Letzter Sync',
            cell: ({ row }) => h('div', { class: '' }, formatDateShort(row.getValue('last_sync_at')) || '—'),
            meta: { headerClass: 'w-fit', cellClass: 'w-fit' },
        },
    ]
}
