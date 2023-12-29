<script setup>
import SidebarCategory from "@/Components/SidebarCategory.vue";
import SidebarTab from "@/Components/SidebarTab.vue";
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";

defineProps({
    users: Object,
});

const page = usePage();

const currentUser = computed(() => {
    return page.props.user.role;
});
</script>

<template>
    <aside
        id="sidebar"
        class="fixed top-0 left-0 z-20 flex flex-col flex-shrink-0 hidden w-64 h-full pt-16 font-normal duration-0 lg:flex transition-width"
        aria-label="Sidebar"
    >
        <div
            class="relative flex flex-col flex-1 min-h-0 py-5 overflow-y-auto bg-white border-r border-gray-200 dark:bg-gray-800 dark:border-gray-700"
        >
            <SidebarCategory
                v-if="$page.props.auth.user.user_type == users.admin ||
                $page.props.auth.user.user_type == users.hr_staff"
                name="Menu"
            >
                <template #tab>
                    <li>
                        <SidebarTab
                            :href="route(`${currentUser}.dashboard.index`)"
                            :class="[
                                $page.url.includes('dashboard') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'gauge']"
                                />
                            </template>

                            <template #title> Dashboard </template>
                        </SidebarTab>
                    </li>
                </template>
            </SidebarCategory>

            <SidebarCategory
                v-if="$page.props.auth.user.user_type == users.hr_manager"
                name="Menu"
            >
                <template #tab>
                    <li>
                        <SidebarTab
                            :href="route(`${currentUser}.reports.index`)"
                            :class="[
                                $page.url.includes('reports') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'gauge']"
                                />
                            </template>

                            <template #title> Reports </template>
                        </SidebarTab>
                    </li>
                </template>
            </SidebarCategory>

            <SidebarCategory
                v-if="
                    $page.props.auth.user.user_type == users.admin ||
                    $page.props.auth.user.user_type == users.hr_manager
                "
                name="Employees"
            >
                <template #tab>
                    <li v-if="$page.props.auth.user.user_type == users.admin">
                        <SidebarTab
                            :href="route(`${currentUser}.hr-managers.index`)"
                            :class="[
                                $page.url.includes('managers') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'user-shield']"
                                />
                            </template>

                            <template #title> HR Managers </template>
                        </SidebarTab>
                    </li>

                    <li>
                        <SidebarTab
                            :href="route(`${currentUser}.hr-staffs.index`)"
                            :class="[
                                $page.url.includes('staffs') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'user']"
                                />
                            </template>

                            <template #title> HR Staffs </template>
                        </SidebarTab>
                    </li>
                </template>
            </SidebarCategory>

            <SidebarCategory
                name="Applicants"
                v-if="
                    $page.props.auth.user.user_type == users.admin
                "
            >
                <template #tab>
                    <li>
                        <SidebarTab
                            :href="route(`${currentUser}.applicants.index`)"
                            :class="[
                                $page.url.includes('applicants') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'user-check']"
                                />
                            </template>

                            <template #title> Hired Employees </template>
                        </SidebarTab>
                    </li>
                </template>
            </SidebarCategory>

            <SidebarCategory
                name="Users"
                v-if="
                    $page.props.auth.user.user_type == users.hr_staff
                "
            >
                <template #tab>
                    <li>
                        <SidebarTab
                            :href="route(`${currentUser}.applicants.index`)"
                            :class="[
                                $page.url.includes('applicants') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'user-check']"
                                />
                            </template>

                            <template #title> Applicants </template>
                        </SidebarTab>
                    </li>
                </template>
            </SidebarCategory>

            <SidebarCategory
                name="Recruitments"
                v-if="

                    $page.props.auth.user.user_type == users.hr_manager
                "
            >
                <template #tab>
                    <li>
                        <SidebarTab
                            :href="route(`${currentUser}.applications.index`)"
                            :class="[
                                $page.url.includes('applications') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'scroll']"
                                />
                            </template>

                            <template #title> Applications </template>
                        </SidebarTab>
                    </li>
                </template>
            </SidebarCategory>

            <SidebarCategory
                name="Recruitments"
                v-if="

                    $page.props.auth.user.user_type == users.hr_staff
                "
            >
                <template #tab>
                    <li>
                        <SidebarTab
                            :href="route(`${currentUser}.applications.index`)"
                            :class="[
                                $page.url.includes('applications') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'scroll']"
                                />
                            </template>

                            <template #title> Applications </template>
                        </SidebarTab>
                    </li>

                    <li>
                        <SidebarTab
                            :href="route(`${currentUser}.for-interview.index`)"
                            :class="[
                                $page.url.includes('/for-interview') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'clipboard-question']"
                                />
                            </template>

                            <template #title> For Interview </template>
                        </SidebarTab>
                    </li>

                    <li>
                        <SidebarTab
                            :href="route(`${currentUser}.in-progress.index`)"
                            :class="[
                                $page.url.includes('/in-progress') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'bars-progress']"
                                />
                            </template>

                            <template #title> In Progress </template>
                        </SidebarTab>
                    </li>

                    <li>
                        <SidebarTab
                            :href="route(`${currentUser}.qualified.index`)"
                            :class="[
                                $page.url.includes('/qualified') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'check']"
                                />
                            </template>

                            <template #title> Qualified </template>
                        </SidebarTab>
                    </li>

                    <li>
                        <SidebarTab
                            :href="route(`${currentUser}.disqualified.index`)"
                            :class="[
                                $page.url.includes('disqualified') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'x']"
                                />
                            </template>

                            <template #title> Disqualified </template>
                        </SidebarTab>
                    </li>

                    <li>
                        <SidebarTab
                            :href="route(`${currentUser}.hired.index`)"
                            :class="[
                                $page.url.includes('hired') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'certificate']"
                                />
                            </template>

                            <template #title> Hired </template>
                        </SidebarTab>
                    </li>
                    
                    <li>
                        <SidebarTab
                            :href="route(`${currentUser}.resigned.index`)"
                            :class="[
                                $page.url.includes('resigned') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'right-from-bracket']"
                                />
                            </template>

                            <template #title> Resigned </template>
                        </SidebarTab>
                    </li>
                </template>
            </SidebarCategory>

            <SidebarCategory
                name="Job Related"
                v-if="$page.props.auth.user.user_type == users.admin"
            >
                <template #tab>
                    <li>
                        <SidebarTab
                            v-if="
                                $page.props.auth.user.user_type == users.admin
                            "
                            :href="route(`${currentUser}.qualifications.index`)"
                            :class="[
                                $page.url.includes('qualifications') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'scroll']"
                                />
                            </template>

                            <template #title> Qualifications </template>
                        </SidebarTab>
                    </li>

                    <li>
                        <SidebarTab
                            v-if="
                                $page.props.auth.user.user_type == users.admin
                            "
                            :href="route(`${currentUser}.benefits.index`)"
                            :class="[
                                $page.url.includes('benefits') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'scroll']"
                                />
                            </template>

                            <template #title> Benefits </template>
                        </SidebarTab>
                    </li>

                    <li>
                        <SidebarTab
                            v-if="
                                $page.props.auth.user.user_type == users.admin
                            "
                            :href="
                                route(
                                    `${currentUser}.company-assignments.index`
                                )
                            "
                            :class="[
                                $page.url.includes('company-assignments') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'scroll']"
                                />
                            </template>

                            <template #title> Company Assignments </template>
                        </SidebarTab>
                    </li>

                    <li>
                        <SidebarTab
                            v-if="
                                $page.props.auth.user.user_type == users.admin
                            "
                            :href="route(`${currentUser}.job-types.index`)"
                            :class="[
                                $page.url.includes('job-types') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'scroll']"
                                />
                            </template>

                            <template #title> Job Types </template>
                        </SidebarTab>
                    </li>

                    <li>
                        <SidebarTab
                            v-if="
                                $page.props.auth.user.user_type == users.admin
                            "
                            :href="route(`${currentUser}.employee-types.index`)"
                            :class="[
                                $page.url.includes('employee-types') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'scroll']"
                                />
                            </template>

                            <template #title> Employee Types </template>
                        </SidebarTab>
                    </li>

                    <li>
                        <SidebarTab
                            v-if="
                                $page.props.auth.user.user_type == users.admin
                            "
                            :href="route(`${currentUser}.industries.index`)"
                            :class="[
                                $page.url.includes('industries') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'scroll']"
                                />
                            </template>

                            <template #title> Industries </template>
                        </SidebarTab>
                    </li>

                    <li>
                        <SidebarTab
                            v-if="
                                $page.props.auth.user.user_type == users.admin
                            "
                            :href="route(`${currentUser}.job-positions.index`)"
                            :class="[
                                $page.url.includes('job-positions') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'scroll']"
                                />
                            </template>

                            <template #title> Job Positions </template>
                        </SidebarTab>
                    </li>
                </template>
            </SidebarCategory>

            <SidebarCategory
                name="Interview Related"
                v-if="
                    $page.props.auth.user.user_type == users.hr_staff
                "
            >
                <template #tab>
                    <li
                >
                        <SidebarTab
                            :href="route(`${currentUser}.appointments.index`)"
                            :class="[
                                $page.url.includes('appointments') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'calendar-check']"
                                />
                            </template>

                            <template #title> Appointments </template>
                        </SidebarTab>
                    </li>

                    <li>
                        <SidebarTab
                            :href="route(`${currentUser}.documents.index`)"
                            :class="[
                                $page.url.includes('documents') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'file']"
                                />
                            </template>

                            <template #title> Documents </template>
                        </SidebarTab>
                    </li>
                </template>
            </SidebarCategory>

            <SidebarCategory
                name="Logs"
                v-if="$page.props.auth.user.user_type == users.admin"
            >
                <template #tab>
                    <li v-if="$page.props.auth.user.user_type == users.admin">
                        <SidebarTab
                            :href="route(`${currentUser}.activity-logs.index`)"
                            :class="[
                                $page.url.includes('activity-logs') &&
                                    'bg-gray-100 dark:bg-gray-700',
                            ]"
                        >
                            <template #icon>
                                <font-awesome-icon
                                    class="w-6 h-6 mr-3 text-gray-500 transition duration-0 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white"
                                    :icon="['fas', 'user-check']"
                                />
                            </template>

                            <template #title> Activity Logs </template>
                        </SidebarTab>
                    </li>
                </template>
            </SidebarCategory>
        </div>
    </aside>

    <div
        class="fixed inset-0 z-10 hidden bg-gray-900/50 dark:bg-gray-900/90"
        id="sidebarBackdrop"
    ></div>
</template>
