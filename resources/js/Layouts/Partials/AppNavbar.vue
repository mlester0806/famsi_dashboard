<script setup>
import NavbarHome from "@/Partials/Navbar/NavbarHome.vue";
import NavbarNotifications from "@/Partials/Navbar/NavbarNotifications.vue";
import NavbarOptions from "@/Partials/Navbar/NavbarOptions.vue";
import NavbarThemes from "@/Partials/Navbar/NavbarThemes.vue";
import { usePage } from "@inertiajs/vue3";

defineProps({
    users: Object,
});

const page = usePage();

console.log(page);
</script>

<template>
    <nav class="fixed z-30 px-6 w-full bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <div class="px-3 py-3 lg:px-5 lg:pl-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center justify-start">
                    <NavbarHome :users="users" />
                </div>
                <div class="flex items-center">
                    <h1 v-if="page.props.user.role === 'admin'" class="mx-4">{{ page.props.user.details.first_name }} {{
                        page.props.user.details.last_name }}</h1>

                    <h1 v-if="page.props.user.role === 'hr-manager'" class="mx-4"><span
                            class="font-bold mr-1">MANAGER</span> {{
                                page.props.user.details.first_name }} {{
        page.props.user.details.last_name }}</h1>

                    <h1 v-if="page.props.user.role === 'hr-staff'" class="mx-4"><span class="font-bold mr-1">STAFF</span> {{
                        page.props.user.details.first_name }} {{
        page.props.user.details.last_name }}</h1>

                    <NavbarNotifications />

                    <NavbarThemes />

                    <div class="flex items-center ml-3">
                        <NavbarOptions :users="users" />
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>
