<script setup>
import TableEmployeesDocuments from "@/Components/TableEmployeesDocuments.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    hired: Object,
    pagination: Object,
    filters: Object,
    jobPositions: Array,
    locations: Array,
});
</script>

<template>
    <AppLayout title="Employees Documents">
        <TableEmployeesDocuments :roles="hired" :pagination="pagination" :filters="filters" :jobPositions="jobPositions"
            :locations="locations" linkName="employees-documents" title="Employees Documents">
            <template #first-tab>
                <p class="inline-flex items-center text-gray-700 dark:text-gray-300">
                    <font-awesome-icon class="w-5 h-5 mr-2.5" :icon="['fas', 'house']" />
                    Recruitments
                </p>
            </template>

            <template #second-tab> Employees Documents </template>

            <template #title>Employees Documents</template>
            <template #description>This section pertains to employees documents applicants.</template>
        </TableEmployeesDocuments>
    </AppLayout>
</template>
