<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import TableHRStaffDashboard from "@/Components/TableHRStaffDashboard.vue";

defineProps({
    applicants: Array,
    events: Array,
});
</script>

<template>
    <AppLayout title="Dashboard">
        <div class="px-4 pt-6">
            <TableHRStaffDashboard
                linkName="appointments"
                :events="events"
                :applicants="applicants"
            >
                <template #title>Schedules for Applicant Interviews</template>
                <template #description
                    >This is your schedules for applicant Interviews</template
                >
            </TableHRStaffDashboard>
        </div>
    </AppLayout>
</template>
