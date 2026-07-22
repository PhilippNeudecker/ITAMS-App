<script setup lang="ts" generic="TData, TValue">
import { ref, computed, defineEmits } from 'vue'
import { valueUpdater } from '@/components/ui/table/utils'
import { Button } from '@/components/ui/button'
import { Skeleton } from '@/components/ui/skeleton'
import { ChevronLeftIcon, ChevronRightIcon } from '@lucide/vue'
import { router } from '@inertiajs/vue3'

import type {
    ColumnDef,
    ColumnFiltersState,
    ExpandedState,
    SortingState,
    VisibilityState,
} from '@tanstack/vue-table'
import {
    FlexRender,
    getCoreRowModel,
    getExpandedRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useVueTable,
} from '@tanstack/vue-table'

import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
    TableFooter,
} from '@/components/ui/table'
import { route } from 'ziggy-js'
import type { Paginator } from '@/interfaces/Pagination'

const props = defineProps<{
    columns: any[] //ColumnDef<TData, TValue>
    paginationData: Paginator<TData>
    loading?: boolean
}>();

const sorting = ref<SortingState>([])
const columnFilters = ref<ColumnFiltersState>([])
const columnVisibility = ref<VisibilityState>({})
const rowSelection = ref({})
const expanded = ref<ExpandedState>(true)

const table = useVueTable({
    get data() { return props.paginationData?.data ?? [] },
    get columns() { return props.columns },
    getCoreRowModel: getCoreRowModel(),
    // getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getExpandedRowModel: getExpandedRowModel(),
    getSubRows: (row: any) => row?.children,
    onSortingChange: updaterOrValue => valueUpdater(updaterOrValue, sorting),
    onColumnFiltersChange: updaterOrValue => valueUpdater(updaterOrValue, columnFilters),
    onColumnVisibilityChange: updaterOrValue => valueUpdater(updaterOrValue, columnVisibility),
    onRowSelectionChange: updaterOrValue => valueUpdater(updaterOrValue, rowSelection),
    onExpandedChange: updaterOrValue => valueUpdater(updaterOrValue, expanded),
    state: {
        get sorting() { return sorting.value },
        get columnFilters() { return columnFilters.value },
        get columnVisibility() { return columnVisibility.value },
        get rowSelection() { return rowSelection.value },
        get expanded() { return expanded.value },
    },
})

const skeletonRowCount = computed(() => props.paginationData?.data.length > 0 ? props.paginationData?.data.length : 15)

function metaClass(def: any, key: 'headerClass' | 'cellClass') {
    return (def?.meta as any)?.[key] ?? ''
}

const emit = defineEmits<{
    (e: 'row-click', row: TData): void
    (e: 'page-change', page: number): void
    (e: 'row-click', row: TData): void
}>()

function goToPage(page: number) {
    emit('page-change', page)
    // router.get(route('employees.index'), {
    //     page,
    //     search: pagination.filters.search
    // }, {
    //     preserveState: true,
    //     preserveScroll: true,
    //     replace: true,
    // })
}
const visibleLinks = computed(() => {
    const links = props.paginationData?.links ?? [];

    const current = props.paginationData?.current_page ?? 1;

    return links.filter(link => {
        if (!Number.isFinite(Number(link.label))) {
            link.label = link.label.replace('Previous', '').replace('Next', '');
            return true;
        }

        const page = Number(link.label);

        return page >= current - 2 && page <= current + 2;
    });
});
</script>

<template>
    <div class="border rounded-md h-full flex flex-col overflow-hidden">
        <div class="flex-1 min-h-0 overflow-auto">
            <Table>
                <TableHeader class="sticky top-0 z-10 bg-background">
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id" :class="metaClass(header.column.columnDef, 'headerClass')">
                            <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="loading">
                        <TableRow v-for="i in skeletonRowCount" :key="`skeleton-${i}`">
                            <TableCell v-for="(col, colIndex) in columns" :key="colIndex" :class="metaClass(col, 'cellClass')">
                                <Skeleton class="h-5 w-full" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else-if="table.getRowModel().rows?.length">
                        <TableRow v-for="row in table.getRowModel().rows" :key="row.id" :data-state="row.getIsSelected() && 'selected'" class="cursor-pointer"
                            @click="emit('row-click', row.original)">
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" :class="metaClass(cell.column.columnDef, 'cellClass')" class="p-1.5">
                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell :colspan="columns.length" class="h-full text-center">
                                Keine Ergebnisse.
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <div class="shrink-0 border-t grid grid-cols-3 items-center justify-between px-4 py-2">
            <div class="flex justify-start">
                <div class="text-sm text-muted-foreground">
                    {{ table.getFilteredSelectedRowModel().rows.length }} von {{ props.paginationData?.total }} Zeile(n) ausgewählt.
                </div>
            </div>
            <div class="flex justify-start gap-1">
                <Button v-for="link in visibleLinks" :key="link.label" :disabled="!link.url" :variant="link.active ? 'default' : 'outline'" @click="router.visit(link.url)"
                    v-html="link.label" />
            </div>
            <div class="flex justify-end">
                <div class="flex items-center gap-4">
                    <div class="text-sm text-muted-foreground" v-if="paginationData?.total > 0">
                        {{ paginationData?.from }}–{{ paginationData?.to }} von {{ paginationData?.total }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
