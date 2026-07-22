<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import DataTable from "@/components/data-table/data-table.vue";
import { buildColumns } from "@/Pages/Manufacturers/columns";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { PlusIcon, SearchIcon, Trash2Icon, SlidersHorizontalIcon, FileSearchIcon, PenIcon, CopyIcon, RefreshCcwIcon } from 'lucide-vue-next';
import { route } from 'ziggy-js';
import FormModal from '@/Pages/Manufacturers/FormModal.vue';
import GenericDeleteModal from '@/components/modals/GenericDeleteModal.vue';
import { type Paginator } from '@/interfaces/Pagination';
import { type ModalMode } from '@/interfaces/ModalMode';

const syncing = ref(false);

const props = defineProps<{
    data: Paginator<any>;
    filters: {
        search?: string;
        active_only?: boolean;
    };
}>();

const search = ref(props.filters.search ?? '');
const activeOnly = ref(props.filters.active_only ? 'active' : '');
let deleteModalDescription = '';

let searchTimer: ReturnType<typeof setTimeout>;
const onSearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => applyFilters(), 350);
};

const applyFilters = () => {
    router.get(route('assets.manufacturers.index'), {
        search: search.value || undefined,
        active_only: activeOnly.value === 'active' || undefined,
    }, { preserveState: true, replace: true, only: ['manufacturers', 'filters'] });
};

const resetFilters = () => {
    search.value = '';
    activeOnly.value = '';
    applyFilters();
};

const hasActiveFilters = computed(() => search.value || activeOnly.value);

const isLoading = ref(false);
let offStart: (() => void) | undefined;
let offFinish: (() => void) | undefined;

onMounted(() => {
    offStart = router.on('start', () => { isLoading.value = true; });
    offFinish = router.on('finish', () => { isLoading.value = false; });
});
onUnmounted(() => {
    offStart?.();
    offFinish?.();
});

const searchExpanded = ref(false);
const searchInputRef = ref<InstanceType<typeof Input> | null>(null);

function expandSearch() {
    searchExpanded.value = true;
    nextTick(() => {
        const el = (searchInputRef.value as any)?.$el ?? searchInputRef.value;
        el?.focus?.();
    });
}

function collapseSearchIfEmpty() {
    if (!search.value) searchExpanded.value = false;
}

const selectedIds = ref<Set<number>>(new Set());

const isSelected = (id: number) => selectedIds.value.has(id);
const toggleSelected = (id: number, value: boolean) => {
    const next = new Set(selectedIds.value);
    value ? next.add(id) : next.delete(id);
    selectedIds.value = next;
};
const isAllSelected = (): boolean | 'indeterminate' => {
    const rows = props.data.data;
    if (!rows.length) return false;
    if (rows.every(t => selectedIds.value.has(t.id))) return true;
    if (rows.some(t => selectedIds.value.has(t.id))) return 'indeterminate';
    return false;
};
const toggleAll = (value: boolean) => {
    const next = new Set(selectedIds.value);
    props.data.data.forEach(m => value ? next.add(m.id) : next.delete(m.id));
    selectedIds.value = next;
};

const selectedDatas = computed(() => props.data.data.filter(t => selectedIds.value.has(t.id)));
const singleSelectedData = computed(() => selectedDatas.value.length === 1 ? selectedDatas.value[0] : null);

const modalOpen = ref(false);
const deleteModalOpen = ref(false);
const modalMode = ref<ModalMode>('create');
const modalData = ref<any | null>(null);

function findTag(id: number): any | null {
    return props.data.data.find(t => t.id === id) ?? null;
}

function openModal(mode: ModalMode, data: any | null) {
    modalMode.value = mode;
    modalData.value = data;
    modalOpen.value = true;
}

function openDeleteModal(data: any[] = selectedDatas.value) {

    const inUse = data.filter(t => (t.assets_count ?? 0) > 0);
    if (inUse.length) {
        alert(`Folgende Hersteller werden noch von Assets verwendet und können nicht gelöscht werden: ${inUse.map(t => t.name).join(', ')}`);
        return;
    }
    deleteModalDescription = data.length === 1
        ? `Möchten Sie den Hersteller "${data[0].name}" wirklich löschen?`
        : `Möchten Sie ${data.length} Hersteller wirklich löschen?`;

    deleteModalOpen.value = true;
}

function handleDeleteSuccess(datas: any[] = selectedDatas.value) {
    const ids = datas.map(t => t.id);

    if (ids.length === 1) {
        router.delete(route('assets.manufacturers.destroy', ids[0]), {
            preserveScroll: true,
            preserveState: true,
            only: ['manufacturers'],
            onSuccess: () => selectedIds.value.delete(ids[0]),
        });
    } else {
        router.delete(route('assets.manufacturers.bulk-destroy'), {
            data: { ids },
            preserveScroll: true,
            preserveState: true,
            only: ['manufacturers'],
            onSuccess: () => { selectedIds.value = new Set(); },
        });
    }
    deleteModalOpen.value = false;
}

const onCreate = () => openModal('create', null);
const onView = (id?: number) => {
    const tag = id ? findTag(id) : singleSelectedData.value;
    if (tag) openModal('view', tag);
};
const onEdit = (id?: number) => {
    const tag = id ? findTag(id) : singleSelectedData.value;
    if (tag) openModal('edit', tag);
};
const onCopy = (id?: number) => {
    const tag = id ? findTag(id) : singleSelectedData.value;
    if (tag) openModal('copy', tag);
};

function onRowClick(row: any) {
    openModal('view', row);
}

function afterSave() {
    router.reload({ only: ['manufacturers'] });
}

function deleteOne(id: number) {
    const tag = findTag(id);
    if (!tag) return;
    openDeleteModal([tag]);
}

const onRefresh = () => {
    router.reload({ only: ['manufacturers', 'filters'] });
};

const columns = computed(() => buildColumns({
    isSelected,
    toggleSelected,
    isAllSelected,
    toggleAll,
    onView,
    onEdit,
    onCopy,
    onDelete: deleteOne,
}));

const onPageChange = (page: number) => {
    router.get(route('employees.index'), {
        page,
        search: props.filters.search,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
};
</script>

<template>
    <DashboardLayout :breadcrumbs="[{ name: 'Assets', href: route('assets.index') }, { name: 'Hersteller', href: route('assets.manufacturers.index') }]">
        <div class="flex flex-col gap-2 h-full min-h-0">
            <!-- Actions -->
            <div class="flex justify-between rounded-md border border-destructive/30 bg-destructive/5 px-4 py-2 shrink-0">
                <div class="flex items-center gap-2">
                    <Button variant="outline" class="text-green-500" @click="onCreate()" title="Neuer Hersteller">
                        <PlusIcon class="w-4 h-4" />
                        <!-- <div class="pe-1">Neu</div> -->
                    </Button>
                    <Separator orientation="vertical" />
                    <Button variant="outline" class="text-gray-500" :disabled="!singleSelectedData" @click="onView()" title="Anzeigen">
                        <FileSearchIcon class="w-4 h-4" />
                        <!-- <div class="pe-1">Anzeigen</div> -->
                    </Button>
                    <Button variant="outline" class="text-gray-500" :disabled="!singleSelectedData" @click="onEdit()" title="Bearbeiten">
                        <PenIcon class="w-4 h-4" />
                        <!-- <div class="pe-1">Bearbeiten</div> -->
                    </Button>
                    <Button variant="outline" class="text-gray-500" :disabled="!singleSelectedData" @click="onCopy()" title="Kopieren">
                        <CopyIcon class="w-4 h-4" />
                        <!-- <div class="pe-1">Kopieren</div> -->
                    </Button>
                    <Separator orientation="vertical" />
                    <Button variant="outline" class="text-red-500" :disabled="!selectedDatas.length" @click="openDeleteModal()" title="Löschen">
                        <Trash2Icon class="w-4 h-4" />
                        <!-- <div class="pe-1">Löschen</div> -->
                    </Button>
                    <Separator orientation="vertical" />
                    <Button variant="outline" class="text-gray-500" @click="onRefresh()" title="Aktualisieren" :disabled="isLoading">
                        <RefreshCcwIcon class="w-4 h-4" :class="{ 'animate-spin': isLoading }" />
                        <!-- <div class="pe-1">Aktualisieren</div> -->
                    </Button>
                </div>
                <div class="flex gap-2 justify-end">
                    <div class="relative flex items-center h-9 overflow-hidden transition-all duration-200 ease-in-out" :class="(searchExpanded || search) ? 'w-64' : 'w-9'">
                        <button v-if="!(searchExpanded || search)" type="button" @click="expandSearch"
                            class="flex items-center justify-center w-9 h-9 shrink-0 rounded-md border text-muted-foreground hover:bg-accent hover:text-accent-foreground transition-colors"
                            title="Suchen nach Name…">
                            <SearchIcon class="h-4 w-4" />
                        </button>
                        <template v-else>
                            <SearchIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" />
                            <Input ref="searchInputRef" v-model="search" @input="onSearch" @blur="collapseSearchIfEmpty" placeholder="Suchen nach Name…" class="pl-9 w-full" />
                        </template>
                    </div>
                    <Button v-if="hasActiveFilters" variant="ghost" size="icon" @click="resetFilters" title="Filter zurücksetzen">
                        <SlidersHorizontalIcon class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <div class="h-1 py-0 gap-0 shrink-0 rounded-full overflow-hidden" :class="isLoading ? 'bg-muted' : ''">
                <div v-if="isLoading" class="h-full w-full bg-primary/70 animate-pulse" />
            </div>

            <div class="flex-1 min-h-0">
                <DataTable :columns="columns" :paginationData="data" :loading="isLoading" @row-click="onRowClick" @page-change="onPageChange" />
            </div>
        </div>

        <FormModal v-model:open="modalOpen" :mode="modalMode" :manufacturer="modalData" @saved="afterSave" />
        <GenericDeleteModal v-model:open="deleteModalOpen" title="Hersteller löschen" :description=deleteModalDescription :data="selectedDatas" @success="handleDeleteSuccess()" />
    </DashboardLayout>
</template>
