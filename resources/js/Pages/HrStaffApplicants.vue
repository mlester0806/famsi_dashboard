<script setup>
import TableHrStaffApplicants from "@/Components/TableHrStaffApplicants.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    applicants: Object,
    pagination: Object,
    filters: Object,
});
</script>

<template>
    <AppLayout title="Applicants">
        <TableHrStaffApplicants
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
                    Users
                </p>
            </template>

            <template #second-tab> Aplicants </template>

            <template #title>Aplicants</template>
            <template #description
                >This section pertains to Aplicants accounts.</template
            >
        </TableHrStaffApplicants>
    </AppLayout>
</template>
