<script setup lang="ts" generic="TData extends { id: string | number }">
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Separator } from "@/components/ui/separator";
import { Search, Plus, X, Trash2, Pen, Copy, RefreshCcw, FileSearch } from "lucide-vue-next";
import { ref, computed } from 'vue';

interface ExtraAction {
    icon: any;
    label: string;
    iconColor?: string;
    onClick: () => void;
    disabled?: boolean | (() => boolean);
}

interface StandardActions {
    create?: boolean;
    view?: boolean;
    edit?: boolean;
    copy?: boolean;
    delete?: boolean;
    refresh?: boolean;
}

interface Props {
    selectedRows?: TData[];
    searchPlaceholder?: string;
    searchValue?: string;
    showSearch?: boolean;
    hideActions?: StandardActions;
    extraActions?: ExtraAction[];
}

const props = withDefaults(defineProps<Props>(), {
    selectedRows: () => [],
    searchPlaceholder: 'Suchen...',
    searchValue: '',
    showSearch: true,
    hideActions: () => ({}),
    extraActions: () => []
});

const emit = defineEmits<{
    'create': [];
    'view': [id: string | number];
    'edit': [id: string | number];
    'copy': [id: string | number];
    'delete': [ids: (string | number)[]];
    'refresh': [];
    'update:searchValue': [value: string];
    'search:clear': [];
}>();

const localSearch = ref(props.searchValue);

const handleSearchInput = (event: Event) => {
    const value = (event.target as HTMLInputElement).value;
    localSearch.value = value;
    emit('update:searchValue', value);
};

const clearSearch = () => {
    localSearch.value = '';
    emit('update:searchValue', '');
    emit('search:clear');
};

const getDisabledState = (action: ExtraAction) => {
    if (typeof action.disabled === 'function') {
        return action.disabled();
    }
    return action.disabled ?? false;
};

const standardActions = computed(() => [
    {
        key: 'create',
        icon: Plus,
        label: 'Neu',
        iconColor: 'text-green-500',
        onClick: () => emit('create'),
        disabled: false,
        show: !props.hideActions?.create
    },
    {
        key: 'view',
        icon: FileSearch,
        label: 'Anzeigen',
        iconColor: 'text-gray-500',
        onClick: () => {
            if (props.selectedRows.length === 1) {
                emit('view', props.selectedRows[0].id);
            }
        },
        disabled: props.selectedRows.length !== 1,
        show: !props.hideActions?.view,
        separator: 'before'
    },
    {
        key: 'edit',
        icon: Pen,
        label: 'Bearbeiten',
        iconColor: 'text-gray-500',
        onClick: () => {
            if (props.selectedRows.length === 1) {
                emit('edit', props.selectedRows[0].id);
            }
        },
        disabled: props.selectedRows.length !== 1,
        show: !props.hideActions?.edit
    },
    {
        key: 'copy',
        icon: Copy,
        label: 'Kopieren',
        iconColor: 'text-gray-500',
        onClick: () => {
            if (props.selectedRows.length === 1) {
                emit('copy', props.selectedRows[0].id);
            }
        },
        disabled: props.selectedRows.length !== 1,
        show: !props.hideActions?.copy,
        separator: 'after'
    },
    {
        key: 'delete',
        icon: Trash2,
        label: 'Löschen',
        iconColor: 'text-red-500',
        onClick: () => {
            const ids = props.selectedRows.map(row => row.id);
            emit('delete', ids);
        },
        disabled: props.selectedRows.length === 0,
        show: !props.hideActions?.delete,
        separator: 'after'
    },
    {
        key: 'refresh',
        icon: RefreshCcw,
        label: 'Aktualisieren',
        iconColor: 'text-gray-500',
        onClick: () => emit('refresh'),
        disabled: false,
        show: !props.hideActions?.refresh,
        separator: props.extraActions.length > 0 ? 'after' : undefined
    }
]);

const visibleStandardActions = computed(() =>
    standardActions.value.filter(action => action.show)
);
</script>

<template>
    <div class="flex justify-between">
        <!-- Actions -->
        <div class="flex items-center gap-2">
            <!-- Standard-Aktionen -->
            <template v-for="(action, index) in visibleStandardActions" :key="action.key">
                <Separator
                    v-if="action.separator === 'before'"
                    orientation="vertical"
                />

                <Button
                    variant="outline"
                    :class="action.iconColor"
                    :disabled="action.disabled"
                    @click="action.onClick"
                >
                    <component :is="action.icon" class="w-4 h-4" />
                    <div class="pe-1">{{ action.label }}</div>
                </Button>

                <Separator
                    v-if="action.separator === 'after'"
                    orientation="vertical"
                />
            </template>

            <!-- Extra-Aktionen -->
            <template v-for="(action, index) in extraActions" :key="'extra-' + index">
                <Button
                    variant="outline"
                    :class="action.iconColor"
                    :disabled="getDisabledState(action)"
                    @click="action.onClick"
                >
                    <component :is="action.icon" class="w-4 h-4" />
                    <div class="pe-1">{{ action.label }}</div>
                </Button>
            </template>
        </div>

        <!-- Search -->
        <div v-if="showSearch" class="relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
            <Button
                v-if="localSearch"
                variant="outline"
                class="absolute right-0 top-1/2 transform -translate-y-1/2"
                @click="clearSearch"
            >
                <X class="w-4 h-4" />
            </Button>
            <Input
                v-model="localSearch"
                :placeholder="searchPlaceholder"
                class="pl-10 pr-20"
                @input="handleSearchInput"
            />
        </div>
    </div>
</template>
