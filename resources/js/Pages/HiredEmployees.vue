<script setup>
import TableCrud from "@/Components/TableCrud.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    applicants: Object,
    pagination: Object,
    filters: Object,
});
</script>

<template>
    <AppLayout title="Applicants">
        <TableCrud
            :roles="applicants"
            :pagination="pagination"
            :filters="filters"
            linkName="applicants"
            title="Applicant"
        >
            <template #first-tab>
                <p
                    class="inline-flex items-center text-gray-700 dark:text-gray-300"
                >
                    <font-awesome-icon
                        class="w-5 h-5 mr-2.5"
                        :icon="['fas', 'house']"
                    />
                    Applicants
                </p>
            </template>

            <template #second-tab> Hired Employees </template>

            <template #title>Hired Employees</template>
            <template #description
                >This section pertains to Hired Employees accounts.</template
            >
        </TableCrud>
    </AppLayout>
</template>
