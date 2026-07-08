<!-- <script setup>
import DashboardLayout from "@/Layouts/DashboardLayout.vue";
import DataTable from "@/components/data-table/data-table.vue";
import { columns, data } from "@/components/assets/columns";
</script>
<template>
    <DashboardLayout :breadcrumbs="[{ name: 'Assets', href: '/assets' }]">
        <DataTable :columns="columns" :data="data" />
    </DashboardLayout>
</template> -->
<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import DataTable from "@/components/data-table/data-table.vue";
import { columns, data } from "@/components/assets/columns";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { PlusIcon, SearchIcon, Trash2Icon, SlidersHorizontalIcon } from 'lucide-vue-next';
import { route } from 'ziggy-js';

// interface Category  { id: string; name: string; color?: string }
// interface Status     { id: string; name: string; color?: string }
// interface Location   { id: string; name: string }
// interface Manufacturer { id: string; name: string }
// interface Employee   { id: string; display_name: string }
// interface Assignment { employee?: Employee }

// interface Asset {
//     id: string;
//     asset_label: string;
//     name: string;
//     category?: Category;
//     status?: Status;
//     location?: Location;
//     manufacturer?: Manufacturer;
//     active_assignment?: Assignment;
//     warranty_end_date?: string;
// }

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

const search      = ref(props.filters.search ?? '');
const categoryId  = ref(props.filters.category_id ?? '');
const statusId    = ref(props.filters.status_id ?? '');
const locationId  = ref(props.filters.location_id ?? '');

let searchTimer: ReturnType<typeof setTimeout>;
const onSearch = () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => applyFilters(), 350);
};

const applyFilters = () => {
    router.get(route('assets.index'), {
        search:      search.value      || undefined,
        category_id: categoryId.value  || undefined,
        status_id:   statusId.value    || undefined,
        location_id: locationId.value  || undefined,
    }, { preserveState: true, replace: true });
};

const resetFilters = () => {
    search.value     = '';
    categoryId.value = '';
    statusId.value   = '';
    locationId.value = '';
    applyFilters();
};

const hasActiveFilters = computed(() =>
    search.value || categoryId.value || statusId.value || locationId.value
);

const selected = ref<Set<string>>(new Set());

const allSelected = computed(() =>
    props.assets.data.length > 0 &&
    props.assets.data.every(a => selected.value.has(a.id))
);

const toggleAll = (checked: boolean) => {
    if (checked) props.assets.data.forEach(a => selected.value.add(a.id));
    else selected.value.clear();
};

const toggleOne = (id: string, checked: boolean) => {
    if (checked) selected.value.add(id);
    else selected.value.delete(id);
};

const deleteSelected = () => {
    if (!selected.value.size) return;
    if (!confirm(`${selected.value.size} Asset(s) wirklich löschen?`)) return;
    router.delete(route('assets.index'), {
        data: { ids: [...selected.value] },
        onSuccess: () => selected.value.clear(),
    });
};

const formatDate = (date?: string) =>
    date ? new Date(date).toLocaleDateString('de-DE') : '—';

const warrantyBadgeVariant = (date?: string) => {
    if (!date) return 'secondary';
    const days = (new Date(date).getTime() - Date.now()) / 86400000;
    if (days < 0)   return 'destructive';
    if (days < 60)  return 'warning';
    return 'secondary';
};
</script>

<template>
    <DashboardLayout>
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>                </div>
                <Button :href="route('assets.create')">
                    <PlusIcon class="h-4 w-4 mr-2" />
                    Neues Asset
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
            <!-- Actions -->
            <div v-if="selected.size > 0" class="flex items-center gap-3 rounded-md border border-destructive/30 bg-destructive/5 px-4 py-2">
                <span class="text-sm text-destructive font-medium">{{ selected.size }} ausgewählt</span>
                <Button variant="destructive" size="sm" @click="deleteSelected">
                    <Trash2Icon class="h-4 w-4 mr-1.5" />
                    Löschen
                </Button>
                <Button variant="ghost" size="sm" @click="selected.clear()">Abwählen</Button>
            </div>

            <!-- Tabelle -->
            <div class="rounded-lg border bg-card overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow class="bg-muted/40">
                            <TableHead class="w-10">
                                <Checkbox
                                    :checked="allSelected"
                                    @update:checked="toggleAll"
                                />
                            </TableHead>
                            <TableHead class="font-medium">Label</TableHead>
                            <TableHead class="font-medium">Name</TableHead>
                            <TableHead class="font-medium">Kategorie</TableHead>
                            <TableHead class="font-medium">Status</TableHead>
                            <TableHead class="font-medium">Standort</TableHead>
                            <TableHead class="font-medium">Hersteller</TableHead>
                            <TableHead class="font-medium">Zugewiesen an</TableHead>
                            <TableHead class="font-medium">Garantieende</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="asset in assets.data"
                            :key="asset.id"
                            class="cursor-pointer hover:bg-muted/40 transition-colors"
                            :class="{ 'bg-muted/20': selected.has(asset.id) }"
                            @click="router.visit(route('assets.show', asset.id))"
                        >
                            <TableCell @click.stop>
                                <Checkbox
                                    :checked="selected.has(asset.id)"
                                    @update:checked="(v:any) => toggleOne(asset.id, v)"
                                />
                            </TableCell>
                            <TableCell class="font-mono text-xs font-medium text-primary">
                                {{ asset.asset_label }}
                            </TableCell>
                            <TableCell class="font-medium">{{ asset.name }}</TableCell>
                            <TableCell>
                                <Badge
                                    v-if="asset.category"
                                    variant="outline"
                                    :style="asset.category.color ? `border-color: ${asset.category.color}40; color: ${asset.category.color}` : ''"
                                >
                                    {{ asset.category.name }}
                                </Badge>
                                <span v-else class="text-muted-foreground">—</span>
                            </TableCell>
                            <TableCell>
                                <Badge
                                    v-if="asset.status"
                                    variant="outline"
                                    :style="asset.status.color ? `border-color: ${asset.status.color}40; color: ${asset.status.color}` : ''"
                                >
                                    {{ asset.status.name }}
                                </Badge>
                                <span v-else class="text-muted-foreground">—</span>
                            </TableCell>
                            <TableCell class="text-sm">
                                {{ asset.location?.name ?? '—' }}
                            </TableCell>
                            <TableCell class="text-sm">
                                {{ asset.manufacturer?.name ?? '—' }}
                            </TableCell>
                            <TableCell class="text-sm">
                                {{ asset.active_assignment?.employee?.display_name ?? '—' }}
                            </TableCell>
                            <TableCell>
                                <Badge :variant="warrantyBadgeVariant(asset.warranty_end_date)">
                                    {{ formatDate(asset.warranty_end_date) }}
                                </Badge>
                            </TableCell>
                        </TableRow>

                        <!-- Empty state -->
                        <TableRow v-if="assets.data.length === 0">
                            <TableCell colspan="9" class="py-16 text-center text-muted-foreground">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="text-lg">Keine Assets gefunden</span>
                                    <span class="text-sm" v-if="hasActiveFilters">
                                        Filter zurücksetzen oder
                                        <button class="underline" @click="resetFilters">alle anzeigen</button>
                                    </span>
                                    <Button v-else variant="outline" size="sm" :href="route('assets.create')" class="mt-2">
                                        <PlusIcon class="h-4 w-4 mr-1.5" />
                                        Erstes Asset anlegen
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <DataTable :columns="columns" :data="data" />

            <!-- Pagination -->
            <div v-if="assets.last_page > 1" class="flex items-center justify-between text-sm text-muted-foreground">
                <span>Seite {{ assets.current_page }} von {{ assets.last_page }}</span>
                <div class="flex gap-1">
                    <Button
                        v-for="link in assets.links"
                        :key="link.label"
                        variant="outline"
                        size="sm"
                        :disabled="!link.url"
                        :class="{ 'bg-primary text-primary-foreground border-primary': link.active }"
                        @click="link.url && router.visit(link.url)"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </DashboardLayout>
</template>
