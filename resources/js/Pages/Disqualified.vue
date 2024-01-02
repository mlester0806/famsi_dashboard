<script setup>
import TableDisqualified from "@/Components/TableDisqualified.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    disqualified: Object,
    pagination: Object,
    filters: Object,
    jobPositions: Array,
});
</script>

<template>
    <AppLayout title="Disqualified">
        <TableDisqualified
            :roles="disqualified"
            :pagination="pagination"
            :filters="filters"
            :jobPositions="jobPositions"
            linkName="disqualified"
            title="Disqualified"
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

            <template #second-tab> Disqualified </template>

            <template #title>Disqualified</template>
            <template #description
                >This section pertains to disqualified applicants.</template
            >
        </TableDisqualified>
    </AppLayout>
</template>
