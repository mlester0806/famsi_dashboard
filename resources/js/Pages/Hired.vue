<script setup>
import TableHired from "@/Components/TableHired.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    hired: Object,
    pagination: Object,
    filters: Object,
    jobPositions: Array,
});
</script>

<template>
    <AppLayout title="Hired">
        <TableHired
            :roles="hired"
            :pagination="pagination"
            :filters="filters"
            :jobPositions="jobPositions"
            linkName="hired"
            title="Hired"
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

            <template #second-tab> Hired </template>

            <template #title>Hired</template>
            <template #description
                >This section pertains to hired applicants.</template
            >
        </TableHired>
    </AppLayout>
</template>
