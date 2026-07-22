<script setup lang="ts">
import { computed, ref } from 'vue';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Loader2 } from 'lucide-vue-next';


const props = defineProps<{
    open: boolean,
    title: string,
    description: string,
    data: any | any[],
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'success', data: any | any[]): void;
}>();

const processing = ref(false);
const localOpen = computed({
    get: () => props.open,
    set: (value) => emit('update:open', value)
});

const onSubmit = () => {
    processing.value = true;
    setTimeout(() => {
    }, 200);
    emit('success', props.data);
};
</script>

<template>
    <Dialog v-model:open="localOpen">
        <DialogContent class="sm:max-w-[425px]">
            <form @submit.prevent="onSubmit">
                <DialogHeader>
                    <DialogTitle>
                        {{ props.title }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ props.description }}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="mt-4">
                    <Button type="button" variant="outline" @click="localOpen = false">
                        Abbrechen
                    </Button>
                    <Button variant="secondary" type="submit" class="bg-red-700 hover:bg-red-700/75 text-white hover:text-white/90">
                        <Loader2 v-if="processing" class="w-4 h-4 mr-2 animate-spin" />
                        Löschen
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
