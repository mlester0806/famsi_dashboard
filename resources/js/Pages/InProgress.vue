<script setup>
import TableApplication from "@/Components/TableApplication.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    applications: Object,
    pagination: Object,
    filters: Object,
    jobPositions: Array,
});
</script>

<template>
    <AppLayout title="In Progress">
        <TableApplication
            :roles="applications"
            :pagination="pagination"
            :filters="filters"
            :jobPositions="jobPositions"
            linkName="in-progress"
            title="In Progress"
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

            <template #second-tab> In Progress </template>

            <template #title>In Progress</template>
            <template #description
                >This section pertains to applications of the
                applicants that is already in progress.</template
            >
        </TableApplication>
    </AppLayout>
</template>
