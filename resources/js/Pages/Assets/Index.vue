<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import DataTable from "@/components/data-table/data-table.vue";
import { columns, data } from "@/Pages/Assets/columns";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Separator } from '@/components/ui/separator';
import { PlusIcon, SearchIcon, Trash2Icon, SlidersHorizontalIcon, FileSearchIcon, PenIcon, CopyIcon, RefreshCcwIcon } from 'lucide-vue-next';
import { route } from 'ziggy-js';

interface Paginator<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    assets: Paginator<any>;
    filters: {
        search?: string;
        category_id?: string;
        status_id?: string;
        location_id?: string;
    };
    categories: any[];
    statuses: any[];
    locations: any[];
}>();

const search = ref(props.filters.search ?? '');
const categoryId = ref(props.filters.category_id ?? '');
const statusId = ref(props.filters.status_id ?? '');
const locationId = ref(props.filters.location_id ?? '');

let searchTimer: ReturnType<typeof setTimeout>;
const onSearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => applyFilters(), 350);
};

const applyFilters = () => {
    router.get(route('assets.index'), {
        search: search.value || undefined,
        category_id: categoryId.value || undefined,
        status_id: statusId.value || undefined,
        location_id: locationId.value || undefined,
    }, { preserveState: true, replace: true });
};

const resetFilters = () => {
    search.value = '';
    categoryId.value = '';
    statusId.value = '';
    locationId.value = '';
    applyFilters();
};

const hasActiveFilters = computed(() =>
    search.value || categoryId.value || statusId.value || locationId.value
);

const selected = ref<Set<string>>(new Set());

const deleteSelected = () => {
    if (!selected.value.size) return;
    if (!confirm(`${selected.value.size} Asset(s) wirklich löschen?`)) return;
    router.delete(route('assets.index'), {
        data: { ids: [...selected.value] },
        onSuccess: () => selected.value.clear(),
    });
};

const warrantyBadgeVariant = (date?: string) => {
    if (!date) return 'secondary';
    const days = (new Date(date).getTime() - Date.now()) / 86400000;
    if (days < 0) return 'destructive';
    if (days < 60) return 'warning';
    return 'secondary';
};

const onCreate = () => {
    router.visit(route('assets.create'));
};
const onView = () => { };
const onEdit = () => { };
const onCopy = () => { };
const onDelete = () => { };
const onRefresh = () => { };
</script>

<template>
    <DashboardLayout>
        <div class="flex flex-col gap-2">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div> </div>
                <Button :href="route('assets.create')">
                    <PlusIcon class="h-4 w-4 mr-2" />
                    Neues Asset
                </Button>
            </div>
            <!-- Actions -->
            <div class="flex items-center gap-3 rounded-md border border-destructive/30 bg-destructive/5 px-4 py-2"><!-- v-if="selected.size > 0" -->
                <Button variant="outline" class="text-green-500" @click="onCreate()">
                    <PlusIcon class="w-4 h-4" />
                    <div class="pe-1">Neu</div>
                </Button>
                <Separator orientation="vertical" />
                <Button variant="outline" class="text-gray-500" @click="onView()">
                    <FileSearchIcon class="w-4 h-4" />
                    <div class="pe-1">Anzeigen</div>
                </Button>
                <Separator orientation="vertical" />
                <Button variant="outline" class="text-gray-500" @click="onEdit()">
                    <PenIcon class="w-4 h-4" />
                    <div class="pe-1">Bearbeiten</div>
                </Button>
                <Separator orientation="vertical" />
                <Button variant="outline" class="text-gray-500" @click="onCopy()">
                    <CopyIcon class="w-4 h-4" />
                    <div class="pe-1">Kopieren</div>
                </Button>
                <Separator orientation="vertical" />
                <Button variant="outline" class="text-red-500" @click="onDelete()">
                    <Trash2Icon class="w-4 h-4" />
                    <div class="pe-1">Löschen</div>
                </Button>
                <Separator orientation="vertical" />
                <Button variant="outline" class="text-gray-500" @click="onRefresh()">
                    <RefreshCcwIcon class="w-4 h-4" />
                    <div class="pe-1">Aktualisieren</div>
                </Button>
            </div>
            <!-- Filter Bar -->
            <div class="flex flex-wrap gap-3 items-end">
                <!-- Suche -->
                <div class="relative flex-1 min-w-52">
                    <SearchIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        @input="onSearch"
                        placeholder="Suchen nach Label, Name…"
                        class="pl-9"
                    />
                </div>

                <!-- Kategorie -->
                <Select v-model="categoryId" @update:modelValue="applyFilters">
                    <SelectTrigger class="w-44">
                        <SelectValue placeholder="Kategorie" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">Alle Kategorien</SelectItem>
                        <SelectItem v-for="c in categories" :key="c.id" :value="c.id">
                            {{ c.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <!-- Status -->
                <Select v-model="statusId" @update:modelValue="applyFilters">
                    <SelectTrigger class="w-40">
                        <SelectValue placeholder="Status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">Alle Status</SelectItem>
                        <SelectItem v-for="s in statuses" :key="s.id" :value="s.id">
                            {{ s.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <!-- Standort -->
                <Select v-model="locationId" @update:modelValue="applyFilters">
                    <SelectTrigger class="w-44">
                        <SelectValue placeholder="Standort" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">Alle Standorte</SelectItem>
                        <SelectItem v-for="l in locations" :key="l.id" :value="l.id">
                            {{ l.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <!-- Reset -->
                <Button v-if="hasActiveFilters" variant="ghost" size="icon" @click="resetFilters" title="Filter zurücksetzen">
                    <SlidersHorizontalIcon class="h-4 w-4" />
                </Button>
            </div>

            <!-- Tabelle -->
            <DataTable :columns="columns" :data="data" />

            <!-- Pagination -->
            <div v-if="assets.last_page > 1" class="flex items-center justify-between text-sm text-muted-foreground">
                <span>Seite {{ assets.current_page }} von {{ assets.last_page }}</span>
                <div class="flex gap-1">
                    <Button v-for="link in assets.links" :key="link.label" variant="outline" size="sm" :disabled="!link.url"
                        :class="{ 'bg-primary text-primary-foreground border-primary': link.active }" @click="link.url && router.visit(link.url)" v-html="link.label" />
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
