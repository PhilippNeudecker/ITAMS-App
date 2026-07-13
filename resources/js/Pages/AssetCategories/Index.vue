<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import DataTable from "@/components/data-table/data-table.vue";
import { buildColumns } from "@/Pages/AssetCategories/columns";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { PlusIcon, SearchIcon, Trash2Icon, SlidersHorizontalIcon, FileSearchIcon, PenIcon, CopyIcon, RefreshCcwIcon } from 'lucide-vue-next';
import { route } from 'ziggy-js';
import CategoryFormModal, { type CategoryModalMode } from '@/Pages/AssetCategories/CategoryFormModal.vue';

interface Paginator<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    categories: Paginator<any>;
    filters: {
        search?: string;
        active_only?: boolean;
    };
}>();

const search = ref(props.filters.search ?? '');
const activeOnly = ref(props.filters.active_only ? 'active' : '');

let searchTimer: ReturnType<typeof setTimeout>;
const onSearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => applyFilters(), 350);
};

const applyFilters = () => {
    router.get(route('assets.categories.index'), {
        search: search.value || undefined,
        active_only: activeOnly.value === 'active' || undefined,
    }, { preserveState: true, replace: true, only: ['Categories', 'filters'] });
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
    const rows = props.categories.data;
    if (!rows.length) return false;
    if (rows.every(t => selectedIds.value.has(t.id))) return true;
    if (rows.some(t => selectedIds.value.has(t.id))) return 'indeterminate';
    return false;
};
const toggleAll = (value: boolean) => {
    const next = new Set(selectedIds.value);
    props.categories.data.forEach(t => value ? next.add(t.id) : next.delete(t.id));
    selectedIds.value = next;
};

const selectedCategories = computed(() => props.categories.data.filter(t => selectedIds.value.has(t.id)));
const singleSelectedCategory = computed(() => selectedCategories.value.length === 1 ? selectedCategories.value[0] : null);

const modalOpen = ref(false);
const modalMode = ref<CategoryModalMode>('create');
const modalCategory = ref<any | null>(null);

function findCategory(id: number): any | null {
    return props.categories.data.find(t => t.id === id) ?? null;
}

function openModal(mode: CategoryModalMode, Category: any | null) {
    modalMode.value = mode;
    modalCategory.value = Category;
    modalOpen.value = true;
}

const onCreate = () => openModal('create', null);
const onView = (id?: number) => {
    const Category = id ? findCategory(id) : singleSelectedCategory.value;
    if (Category) openModal('view', Category);
};
const onEdit = (id?: number) => {
    const Category = id ? findCategory(id) : singleSelectedCategory.value;
    if (Category) openModal('edit', Category);
};
const onCopy = (id?: number) => {
    const Category = id ? findCategory(id) : singleSelectedCategory.value;
    if (Category) openModal('copy', Category);
};

function onRowClick(Category: any) {
    openModal('view', Category);
}

function afterSave() {
    router.reload({ only: ['Categories'], preserveScroll: true });
}

function deleteOne(id: number) {
    const Category = findCategory(id);
    if (!Category) return;
    performDelete([Category]);
}

const onDelete = () => {
    if (!selectedCategories.value.length) return;
    performDelete(selectedCategories.value);
};

function performDelete(Categories: any[]) {
    const inUse = Categories.filter(t => (t.assets_count ?? 0) > 0);
    if (inUse.length) {
        alert(`Folgende Kategorien werden noch von Assets verwendet und können nicht gelöscht werden: ${inUse.map(t => t.name).join(', ')}`);
        return;
    }

    const message = Categories.length === 1
        ? `Möchten Sie die Kategorie "${Categories[0].name}" wirklich löschen?`
        : `Möchten Sie ${Categories.length} Kategorien wirklich löschen?`;

    if (!confirm(message)) return;

    const ids = Categories.map(t => t.id);

    if (ids.length === 1) {
        router.delete(route('assets.categories.destroy', ids[0]), {
            preserveScroll: true,
            preserveState: true,
            only: ['Categories'],
            onSuccess: () => selectedIds.value.delete(ids[0]),
        });
        return;
    }

    router.delete(route('assets.categories.bulk-destroy'), {
        data: { ids },
        preserveScroll: true,
        preserveState: true,
        only: ['Categories'],
        onSuccess: () => { selectedIds.value = new Set(); },
    });
}

const onRefresh = () => {
    router.reload({ only: ['Categories', 'filters'], preserveScroll: true, preserveState: true });
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
</script>

<template>
    <DashboardLayout :breadcrumbs="[{ name: 'Assets', href: route('assets.index') }, { name: 'Kategorien', href: route('assets.categories.index') }]">
        <div class="flex flex-col gap-2 h-full min-h-0">
            <!-- Actions -->
            <div class="flex justify-between rounded-md border border-destructive/30 bg-destructive/5 px-4 py-2 shrink-0">
                <div class="flex items-center gap-2">
                    <Button variant="outline" class="text-green-500" @click="onCreate()" title="Neuer Category">
                        <PlusIcon class="w-4 h-4" />
                        <!-- <div class="pe-1">Neu</div> -->
                    </Button>
                    <Separator orientation="vertical" />
                    <Button variant="outline" class="text-gray-500" :disabled="!singleSelectedCategory" @click="onView()" title="Anzeigen">
                        <FileSearchIcon class="w-4 h-4" />
                        <!-- <div class="pe-1">Anzeigen</div> -->
                    </Button>
                    <Button variant="outline" class="text-gray-500" :disabled="!singleSelectedCategory" @click="onEdit()" title="Bearbeiten">
                        <PenIcon class="w-4 h-4" />
                        <!-- <div class="pe-1">Bearbeiten</div> -->
                    </Button>
                    <Button variant="outline" class="text-gray-500" :disabled="!singleSelectedCategory" @click="onCopy()" title="Kopieren">
                        <CopyIcon class="w-4 h-4" />
                        <!-- <div class="pe-1">Kopieren</div> -->
                    </Button>
                    <Separator orientation="vertical" />
                    <Button variant="outline" class="text-red-500" :disabled="!selectedCategories.length" @click="onDelete()" title="Löschen">
                        <Trash2Icon class="w-4 h-4" />
                        <!-- <div class="pe-1">Löschen</div> -->
                    </Button>
                    <Separator orientation="vertical" />
                    <Button variant="outline" class="text-gray-500" @click="onRefresh()" title="Aktualisieren" :disabled="isLoading">
                        <RefreshCcwIcon class="w-4 h-4" />
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
                    <Select v-model="activeOnly" @update:modelValue="applyFilters">
                        <SelectTrigger class="w-40">
                            <SelectValue placeholder="Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">Alle Status</SelectItem>
                            <SelectItem value="active">Nur aktive</SelectItem>
                        </SelectContent>
                    </Select>
                    <Button v-if="hasActiveFilters" variant="ghost" size="icon" @click="resetFilters" title="Filter zurücksetzen">
                        <SlidersHorizontalIcon class="h-4 w-4" />
                    </Button>
                </div>
            </div>

            <div class="h-1 py-0 gap-0 shrink-0 rounded-full overflow-hidden" :class="isLoading ? 'bg-muted' : ''">
                <div v-if="isLoading" class="h-full w-full bg-primary/70 animate-pulse" />
            </div>

            <div class="flex-1 min-h-0">
                <DataTable :columns="columns" :data="categories.data" :loading="isLoading" @row-click="onRowClick" />
            </div>
        </div>

        <CategoryFormModal v-model:open="modalOpen" :mode="modalMode" :Category="modalCategory" @saved="afterSave" />
    </DashboardLayout>
</template>
