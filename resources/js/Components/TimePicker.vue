<script setup>
import { onMounted } from "vue";
import { Timepicker, Input } from "tw-elements";

const props = defineProps({
    id: String,
    modelValue: String,
    label: String,
    error: String,
});

onMounted(() => {
    const myInput = new Input(document.getElementById(props.id));
    const options = {
        format12: true,
        showClearBtn: false,
        enableValidation: false,
        readOnly: true,
    };
    const myTimepicker = new Timepicker(
        document.getElementById(props.id),
        options
    );
});
</script>

<template>
    <div
        :id="props.id"
        class="relative"
        data-te-timepicker-init
        data-te-input-wrapper-init
    >
        <input
            :id="props.id"
            type="text"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            class="peer block min-h-[auto] w-full rounded border-0 bg-transparent px-3 py-[0.32rem] leading-[2.15] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 data-[te-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-neutral-200 dark:placeholder:text-neutral-200 [&:not([data-te-input-placeholder-active])]:placeholder:opacity-0"
        />
        <label
            :for="props.id"
            class="pointer-events-none absolute left-3 top-0 mb-0 max-w-[90%] origin-[0_0] truncate pt-[0.37rem] leading-[1.6] text-neutral-500 transition-all duration-200 ease-out peer-focus:-translate-y-[0.9rem] peer-focus:scale-[0.8] peer-focus:text-primary peer-data-[te-input-state-active]:-translate-y-[0.9rem] peer-data-[te-input-state-active]:scale-[0.8] motion-reduce:transition-none dark:text-neutral-200 dark:peer-focus:text-primary"
            >{{ props.label }}</label
        >
    </div>
    <p class="text-red-500 text-xs mt-1 absolute" v-if="error">
        {{ error }}
    </p>
</template>
