<script setup>
import TableQualified from "@/Components/TableQualified.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    qualified: Object,
    pagination: Object,
    filters: Object,
    jobPositions: Array,
});
</script>

<template>
    <AppLayout title="Qualified">
        <TableQualified
            :roles="qualified"
            :pagination="pagination"
            :filters="filters"
            :jobPositions="jobPositions"
            linkName="qualified"
            title="Qualified"
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

            <template #second-tab> Qualified </template>

            <template #title>Qualified</template>
            <template #description
                >This section pertains to qualified applicants.</template
            >
        </TableQualified>
    </AppLayout>
</template>
