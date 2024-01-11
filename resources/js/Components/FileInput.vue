<script setup>
import { onMounted } from 'vue';

const props = defineProps({
    id: String,
    modelValue: String,
    label: String,
    placeholder: String,
    disabled: Boolean,
    errors: String,
});

onMounted(async () => {
    await useTwElements();
});

async function useTwElements() {
    const { Input } = await import('tw-elements');

    const myInput = new Input(document.getElementById(props.id));
}
</script>

<template>
    <div class="mb-8">
        <label :for="props.id" class="mb-2 inline-block text-neutral-700 dark:text-neutral-200">{{ props.label }}</label>
        <input :id="props.id" type="file" accept="application/pdf"
            class="relative m-0 block w-full min-w-0 flex-auto cursor-pointer rounded border border-solid border-neutral-300 bg-clip-padding px-3 py-[0.32rem] font-normal leading-[2.15] text-neutral-700 transition duration-300 ease-in-out file:-mx-3 file:-my-[0.32rem] file:cursor-pointer file:overflow-hidden file:rounded-none file:border-0 file:border-solid file:border-inherit file:bg-neutral-100 file:px-3 file:py-[0.32rem] file:text-neutral-700 file:transition file:duration-150 file:ease-in-out file:[border-inline-end-width:1px] file:[margin-inline-end:0.75rem] hover:file:bg-neutral-200 focus:border-primary focus:text-neutral-700 focus:shadow-te-primary focus:outline-none dark:border-neutral-600 dark:text-neutral-200 dark:file:bg-neutral-700 dark:file:text-neutral-100 dark:focus:border-primary"
            @change="$emit('update:modelValue', $event.target.files[0])" :disabled="props.disabled" />
    </div>
    <p class="text-red-500 text-sm absolute -mt-2" v-if="errors">
        {{ errors }}
    </p>
</template>
