import { h } from 'vue'
import type { ColumnDef } from '@tanstack/vue-table'
import { CheckIcon, XIcon } from 'lucide-vue-next'
import { Checkbox } from '@/components/ui/checkbox'
import { Badge } from '@/components/ui/badge'
import DropdownAction from '@/components/data-table/DataTableDropDown.vue'
import { formatDate, formatDateShort } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { ChevronDownIcon, ChevronRightIcon } from '@lucide/vue'

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
            cell: ({ row }) => {
                const canExpand = row.getCanExpand()
                return h('div', {
                    class: 'flex items-center gap-1',
                    style: { paddingLeft: `${row.depth * 1.5}rem` },
                }, [
                    canExpand
                        ? h(Button, {
                            variant: 'ghost',
                            size: 'icon',
                            class: 'h-5 w-5 shrink-0',
                            onClick: (e: Event) => { e.stopPropagation(); row.toggleExpanded() },
                        }, () => h(row.getIsExpanded() ? ChevronDownIcon : ChevronRightIcon, { class: 'h-3.5 w-3.5' }))
                        : h('span', { class: 'inline-block w-5 shrink-0' }),
                    h('span', { class: 'font-medium' }, row.original.name),
                ])
            },
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
            accessorKey: 'description',
            header: () => 'Beschreibung',
            cell: ({ row }) => h('div', { class: 'text-muted-foreground' }, row.getValue('description') || '—'),
            meta: { headerClass: 'w-fit', cellClass: 'w-fit' },
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
            cell: ({ row }) => h('div', {}, row.getValue('asset_prefix') || '—'),
            meta: { headerClass: 'w-px whitespace-nowrap text-center', cellClass: 'w-px whitespace-nowrap text-center' },
        },
    ]
}
