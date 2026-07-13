<script setup lang="ts" generic="TData, TValue">
import { ref, computed } from 'vue'
import { valueUpdater } from '@/components/ui/table/utils'
import { Button } from '@/components/ui/button'
import { Skeleton } from '@/components/ui/skeleton'
import { ChevronLeftIcon, ChevronRightIcon } from '@lucide/vue'

import type {
    ColumnDef,
    ColumnFiltersState,
    SortingState,
    VisibilityState,
} from '@tanstack/vue-table'
import {
    FlexRender,
    getCoreRowModel,
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

const props = defineProps<{
    columns: ColumnDef<TData, TValue>[]
    data: TData[]
    loading?: boolean
}>()

const emit = defineEmits<{
    (e: 'row-click', row: TData): void
}>()

const sorting = ref<SortingState>([])
const columnFilters = ref<ColumnFiltersState>([])
const columnVisibility = ref<VisibilityState>({})
const rowSelection = ref({})

const table = useVueTable({
    get data() { return props.data },
    get columns() { return props.columns },
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    onSortingChange: updaterOrValue => valueUpdater(updaterOrValue, sorting),
    onColumnFiltersChange: updaterOrValue => valueUpdater(updaterOrValue, columnFilters),
    onColumnVisibilityChange: updaterOrValue => valueUpdater(updaterOrValue, columnVisibility),
    onRowSelectionChange: updaterOrValue => valueUpdater(updaterOrValue, rowSelection),
    state: {
        get sorting() { return sorting.value },
        get columnFilters() { return columnFilters.value },
        get columnVisibility() { return columnVisibility.value },
        get rowSelection() { return rowSelection.value },
    },
})

const skeletonRowCount = computed(() => props.data.length > 0 ? Math.min(props.data.length, 10) : 8)

function metaClass(def: any, key: 'headerClass' | 'cellClass') {
    return (def?.meta as any)?.[key] ?? ''
}
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
                                <Skeleton class="h-4 w-full" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else-if="table.getRowModel().rows?.length">
                        <TableRow v-for="row in table.getRowModel().rows" :key="row.id" :data-state="row.getIsSelected() && 'selected'" class="cursor-pointer"
                            @click="emit('row-click', row.original)">
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" :class="metaClass(cell.column.columnDef, 'cellClass')">
                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell :colspan="columns.length" class="h-24 text-center">
                                Keine Ergebnisse.
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <!-- Footer/Pagination bleibt außerhalb des Scrollbereichs am unteren
             Rand der Seite, unabhängig davon wie viele Zeilen geladen sind. -->
        <div class="shrink-0 border-t flex items-center justify-between px-4 py-2">
            <div class="text-sm text-muted-foreground">
                {{ table.getFilteredSelectedRowModel().rows.length }} von
                {{ table.getFilteredRowModel().rows.length }} Zeile(n) ausgewählt.
            </div>
            <div class="flex gap-2">
                <Button variant="outline" size="sm" :disabled="!table.getCanPreviousPage()" @click="table.previousPage()">
                    <ChevronLeftIcon class="w-4 h-4" />
                </Button>
                <!-- <Button size="sm" v-for="page in visiblePages" :key="page" @click="goToPage(page)" :variant="page === currentPage ? 'default' : 'outline'">
                                {{ page }}
                            </Button> -->
                <Button variant="outline" size="sm" :disabled="!table.getCanNextPage()" @click="table.nextPage()">
                    <ChevronRightIcon class="w-4 h-4" />
                </Button>
            </div>
        </div>
    </div>
</template>
