<script setup lang="ts">
import { computed, watch, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Loader2 } from 'lucide-vue-next';
import { type ModalMode } from '@/interfaces/ModalMode';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const processing = ref(false);
const props = defineProps<{
    open: boolean;
    mode: ModalMode;
    location?: any | null;
    parents: any[];
    locationTypes: any[];
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'saved'): void;
}>();

const isReadOnly = computed(() => props.mode === 'view');

const titles: Record<ModalMode, string> = {
    create: 'Neuer Lagerort',
    edit: 'Lagerort bearbeiten',
    view: 'Lagerort ansehen',
    copy: 'Lagerort kopieren',
};

const descriptions: Record<ModalMode, string> = {
    create: 'Lege einen neuen Lagerort an.',
    edit: 'Ändere die Angaben zu diesem Lagerort.',
    view: 'Details zu diesem Lagerort.',
    copy: 'Erstellt eine Kopie dieses Lagerorts.',
};

const form = useForm({
    name: '',
    description: '',
    parent_location_id: null as string | null,
    location_type_definition_id: null as string | null,
    street: '',
    house_number: '',
    postal_code: '',
    city: '',
    country: '',
    additional_info: '',
});

function resetForm() {
    const source = props.location;

    if ((props.mode === 'edit' || props.mode === 'view') && source) {
        form.name = source.name;
        form.description = source.description;
        form.parent_location_id = source.parent_location_id;
        form.location_type_definition_id = source.location_type_definition_id;
        form.street = source.street;
        form.house_number = source.house_number;
        form.postal_code = source.postal_code;
        form.city = source.city;
        form.country = source.country;
        form.additional_info = source.additional_info;
    } else if (props.mode === 'copy' && source) {
        form.name = `${source.name} (Kopie)`;
        form.description = source.description;
        form.parent_location_id = source.parent_location_id;
        form.location_type_definition_id = source.location_type_definition_id;
        form.street = source.street;
        form.house_number = source.house_number;
        form.postal_code = source.postal_code;
        form.city = source.city;
        form.country = source.country;
        form.additional_info = source.additional_info;
    } else {
        form.name = '';
        form.description = '';
        form.parent_location_id = '';
        form.location_type_definition_id = '';
        form.street = '';
        form.house_number = '';
        form.postal_code = '';
        form.city = '';
        form.country = 'Deutschland';
        form.additional_info = '';
    }

    form.clearErrors();
}

watch(() => [props.open, props.mode, props.location], ([open]) => {
    if (open) resetForm();
}, { immediate: true });

function close() {
    emit('update:open', false);
}

function onSubmit() {
    if (isReadOnly.value) {
        close();
        return;
    }
    processing.value = true;

    if (props.mode === 'edit' && props.location) {
        form.patch(route('locations.update', props.location.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                emit('saved');
                close();
            },
        });
        processing.value = false;
        return;
    }

    form.post(route('locations.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            emit('saved');
            close();
        },
    });
    processing.value = false;
}
</script>

<template>
    <Dialog :open="open" @update:open="(v: boolean) => emit('update:open', v)">
        <DialogContent class="sm:max-w-xl">
            <form @submit.prevent="onSubmit">
                <DialogHeader>
                    <DialogTitle>{{ titles[mode] }}</DialogTitle>
                    <DialogDescription>{{ descriptions[mode] }}</DialogDescription>
                </DialogHeader>

                <div class="flex flex-col gap-4 py-2 max-h-[70vh] overflow-y-auto pr-1">
                    <div class="grid gap-2">
                        <Label for="location-name">Name</Label>
                        <Input id="location-name" v-model="form.name" :disabled="isReadOnly" placeholder="Name des Lagerorts" />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="location-description">Beschreibung</Label>
                        <Input id="location-description" v-model="form.description" :disabled="isReadOnly" type="text" placeholder="Beschreibung" />
                        <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="location-parent_location_id">Übergeordneter Lagerort</Label>
                        <Select v-model="form.parent_location_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Keinen auswählen" />
                            </SelectTrigger>

                            <SelectContent>
                                <SelectItem v-for="parent in props.parents" :key="parent.id" :value="parent.id">
                                    {{ parent.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.parent_location_id" class="text-sm text-destructive">
                            {{ form.errors.parent_location_id }}
                        </p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="location-location_type_definition_id">Lagerart</Label>
                        <Select v-model="form.location_type_definition_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Lagerart wählen" />
                            </SelectTrigger>

                            <SelectContent>
                                <SelectItem v-for="type in props.locationTypes" :key="type.id" :value="type.id">
                                    {{ type.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <p v-if="form.errors.location_type_definition_id" class="text-sm text-destructive">
                            {{ form.errors.location_type_definition_id }}
                        </p>
                    </div>

                    <div class="grid gap-2">
                        <Card>
                            <Label for="location-street">Straße</Label>
                            <Input id="location-street" v-model="form.street" :disabled="isReadOnly" placeholder="Straße" />
                            <p v-if="form.errors.street" class="text-sm text-destructive">{{ form.errors.street }}</p>

                            <Label for="location-house_number">Hausnr.</Label>
                            <Input id="location-house_number" v-model="form.house_number" :disabled="isReadOnly" placeholder="#" />
                            <p v-if="form.errors.house_number" class="text-sm text-destructive">{{ form.errors.house_number }}</p>

                            <Label for="location-postal_code">PLZ</Label>
                            <Input id="location-postal_code" v-model="form.postal_code" :disabled="isReadOnly" placeholder="PLZ" />
                            <p v-if="form.errors.postal_code" class="text-sm text-destructive">{{ form.errors.postal_code }}</p>

                            <Label for="location-city">Ort</Label>
                            <Input id="location-city" v-model="form.city" :disabled="isReadOnly" placeholder="Ort" />
                            <p v-if="form.errors.city" class="text-sm text-destructive">{{ form.errors.city }}</p>

                            <Label for="location-country">Land</Label>
                            <Input id="location-country" v-model="form.country" :disabled="isReadOnly" placeholder="Land" />
                            <p v-if="form.errors.country" class="text-sm text-destructive">{{ form.errors.country }}</p>
                        </Card>
                    </div>
                    <div class="grid gap-2">
                        <Label for="location-additional_info">Zusätzliche Info</Label>
                        <Input id="location-additional_info" v-model="form.additional_info" :disabled="isReadOnly" placeholder="Zusätzliche Infos" />
                        <p v-if="form.errors.additional_info" class="text-sm text-destructive">{{ form.errors.additional_info }}</p>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" type="button" @click="close">
                        {{ isReadOnly ? 'Schließen' : 'Abbrechen' }}
                    </Button>
                    <Button v-if="!isReadOnly" :disabled="form.processing" type="submit">
                        <Loader2 v-if="processing" class="w-4 h-4 mr-2 animate-spin" />
                        Speichern
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
