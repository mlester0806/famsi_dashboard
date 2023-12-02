<script setup>
import TableResigned from "@/Components/TableResigned.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    resigned: Object,
    pagination: Object,
    filters: Object,
    jobPositions: Array,
});
</script>

<template>
    <AppLayout title="Resigned">
        <TableResigned
            :roles="resigned"
            :pagination="pagination"
            :filters="filters"
            :jobPositions="jobPositions"
            linkName="resigned"
            title="Resigned"
        >
            <template #first-tab>
                <p
                    class="inline-flex items-center text-gray-700 dark:text-gray-300"
                >
                    <font-awesome-icon
                        class="w-5 h-5 mr-2.5"
                        :icon="['fas', 'house']"
                    />
                    Recruitments
                </p>
            </template>

            <template #second-tab> Resigned </template>

            <template #title>Resigned</template>
            <template #description
                >This section pertains to resigned applicants.</template
            >
        </TableResigned>
    </AppLayout>
</template>
