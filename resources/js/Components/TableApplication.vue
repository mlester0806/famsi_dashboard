<script setup>
import { router, usePage, useForm } from "@inertiajs/vue3";
import { ref, watch, Transition, Teleport } from "vue";
import debounce from "lodash.debounce";
import { useToast } from "vue-toastification";
import Pagination from "../Partials/Table/Pagination.vue";
import InputField from "./InputField.vue";

const props = defineProps({
    roles: Object,
    pagination: Object,
    filters: Object,
    linkName: String,
    title: String,
});

const form = useForm({
    first_name: "",
    middle_name: "",
    last_name: "",
    gender: "",
    email: "",
    contact_number: "",
    job_id: "",
    job_title: "",
    location: "",
    resume_file: "",
    resume_name: "",
    application_status: "",
});

const page = usePage();

const toast = useToast();

let search = ref(props.filters.search);

let currentUpdatingUserID = ref(null);
let viewInfoModalVisibility = ref(false);
let approveModalVisibility = ref(false);
let disapproveModalVisibility = ref(false);

const approve = () => {
    form.put(
        `/${page.props.user.role}/${props.linkName}/approve/${currentUpdatingUserID.value}`,
        {
            onSuccess: () => {
                toast.success("Application approved successfully!");
                hideApproveModal();
                hideDisapproveModal();
                form.reset();
                clearErrors();
            },
        }
    );
};

const disapprove = () => {
    form.put(
        `/${page.props.user.role}/${props.linkName}/disapprove/${currentUpdatingUserID.value}`,
        {
            onSuccess: () => {
                toast.success("Application disapproved successfully!");
                hideApproveModal();
                hideDisapproveModal();
                form.reset();
                clearErrors();
            },
        }
    );
};

// Approve Modal
const showApproveModal = (id) => {
    document.body.classList.remove("overflow-hidden");
    viewInfoModalVisibility.value = false;

    document.body.classList.add("overflow-hidden");

    currentUpdatingUserID.value = id;

    approveModalVisibility.value = true;
};

const hideApproveModal = () => {
    document.body.classList.remove("overflow-hidden");

    currentUpdatingUserID.value = null;

    viewInfoModalVisibility.value = false;
    approveModalVisibility.value = false;
};

// Disapprove Modal
const showDisapproveModal = (id) => {
    document.body.classList.remove("overflow-hidden");
    viewInfoModalVisibility.value = false;

    document.body.classList.add("overflow-hidden");

    currentUpdatingUserID.value = id;

    disapproveModalVisibility.value = true;
};

const hideDisapproveModal = () => {
    document.body.classList.remove("overflow-hidden");

    currentUpdatingUserID.value = null;

    viewInfoModalVisibility.value = false;
    disapproveModalVisibility.value = false;
};

// Show Info Modal
const showInfoModal = (data) => {
    form.first_name = data.first_name;
    form.middle_name = data.middle_name;
    form.last_name = data.last_name;
    form.gender = data.gender;
    form.email = data.email;
    form.contact_number = data.contact_number;
    form.job_id = data.job_id;
    form.job_title = data.title;
    form.location = data.location;
    form.resume_name = data.file_name;
    form.resume_file = data.file_path;
    form.application_status = data.status;

    document.body.classList.add("overflow-hidden");

    currentUpdatingUserID.value = data.id;

    viewInfoModalVisibility.value = true;
};

const hideInfoModal = () => {
    document.body.classList.remove("overflow-hidden");

    currentUpdatingUserID.value = null;

    viewInfoModalVisibility.value = false;

    form.reset();

    form.clearErrors();
};

watch(
    search,
    debounce((value) => {
        const query = {};
        if (value) {
            query.search = value;
        }

        router.get(`/${page.props.user.role}/${props.linkName}`, query, {
            preserveState: true,
            replace: true,
        });
    }, 500)
);
</script>

<template>
    <Transition
        enter-active-class="transition ease-in-out duration-300"
        leave-active-class="transition ease-in-out duration-300"
    >
        <Teleport to="body">
            <div
                v-if="approveModalVisibility"
                @click="hideApproveModal"
                class="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-30 transition duration-200"
            ></div>

            <div
                v-else-if="disapproveModalVisibility"
                @click="hideDisapproveModal"
                class="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-30 transition duration-200"
            ></div>

            <div
                v-else-if="viewInfoModalVisibility"
                @click="hideInfoModal"
                class="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-30 transition duration-200"
            ></div>
        </Teleport>
    </Transition>
    <div
        class="p-4 bg-white block sm:flex items-center justify-between border-b border-gray-200 lg:mt-1.5 dark:bg-gray-800 dark:border-gray-700"
    >
        <div class="w-full mb-1">
            <div class="mb-4">
                <nav class="flex mb-5" aria-label="Breadcrumb">
                    <ol
                        class="inline-flex items-center space-x-1 text-sm font-medium md:space-x-2"
                    >
                        <li class="inline-flex items-center">
                            <slot name="first-tab"></slot>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg
                                    class="w-6 h-6 text-gray-400"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd"
                                    ></path>
                                </svg>
                                <span
                                    class="ml-1 text-gray-400 md:ml-2 dark:text-gray-500"
                                    aria-current="page"
                                    ><slot name="second-tab"></slot
                                ></span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1
                    class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white"
                >
                    <slot name="title"></slot>
                </h1>
                <span
                    class="text-base font-normal text-gray-500 dark:text-gray-400"
                    ><slot name="description"></slot
                ></span>
            </div>
            <div
                class="items-center justify-between block sm:flex md:divide-x md:divide-gray-100 dark:divide-gray-700"
            >
                <div class="flex items-center mb-4 sm:mb-0">
                    <div class="relative w-48 mt-1 sm:w-64 xl:w-96">
                        <InputField
                            id="search"
                            v-model="search"
                            type="search"
                            label="Search"
                            placeholder="Search"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="flex flex-col">
        <div class="overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
                <div class="overflow-hidden shadow">
                    <table
                        class="min-w-full divide-y divide-gray-200 table-fixed dark:divide-gray-600"
                    >
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th
                                    scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400"
                                >
                                    First Name
                                </th>
                                <th
                                    scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Middle Name
                                </th>
                                <th
                                    scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Last Name
                                </th>
                                <th
                                    scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Gender
                                </th>
                                <th
                                    scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Email Address
                                </th>
                                <th
                                    scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Contact Number
                                </th>

                                <th
                                    scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Job ID
                                </th>

                                <th
                                    scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Job Title
                                </th>

                                <th
                                    scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Location
                                </th>

                                <th
                                    scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Application Status
                                </th>

                                <th
                                    scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700"
                        >
                            <tr
                                v-for="(role, index) in roles.data"
                                :key="role.id"
                                @click="showInfoModal(roles.data[index])"
                                class="hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer"
                            >
                                <td
                                    class="p-4 text-sm font-medium text-gray-500 whitespace-nowrap dark:text-gray-400"
                                >
                                    <div
                                        class="text-base text-gray-900 dark:text-white"
                                    >
                                        {{ role.first_name }}
                                    </div>
                                </td>
                                <td
                                    class="px-2 py-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    <div
                                        v-if="role.middle_name"
                                        class="text-base text-gray-900 dark:text-white"
                                    >
                                        {{ role.middle_name }}
                                    </div>

                                    <div
                                        v-else
                                        class="text-base font-light text-gray-400 dark:text-gray-600"
                                    >
                                        N/A
                                    </div>
                                </td>
                                <td
                                    class="px-2 py-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    <div
                                        class="text-base text-gray-900 dark:text-white"
                                    >
                                        {{ role.last_name }}
                                    </div>
                                </td>
                                <td
                                    class="px-2 py-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    <div
                                        class="text-base text-gray-900 dark:text-white"
                                    >
                                        {{ role.gender }}
                                    </div>
                                </td>
                                <td
                                    class="max-w-sm px-2 py-4 overflow-hidden text-base font-normal text-gray-500 truncate xl:max-w-xs dark:text-gray-400"
                                >
                                    <div
                                        class="text-base text-gray-900 dark:text-white"
                                    >
                                        {{ role.email }}
                                    </div>
                                </td>

                                <td
                                    class="max-w-sm px-2 py-4 overflow-hidden text-base font-normal text-gray-500 truncate xl:max-w-xs dark:text-gray-400"
                                >
                                    <div
                                        class="text-base text-gray-900 dark:text-white"
                                    >
                                        {{ role.contact_number }}
                                    </div>
                                </td>

                                <td
                                    class="max-w-sm px-2 py-4 overflow-hidden text-base font-normal text-gray-500 truncate xl:max-w-xs dark:text-gray-400"
                                >
                                    <div
                                        class="text-base text-gray-900 dark:text-white"
                                    >
                                        {{ role.job_id }}
                                    </div>
                                </td>

                                <td
                                    class="max-w-sm px-2 py-4 overflow-hidden text-base font-normal text-gray-500 truncate xl:max-w-xs dark:text-gray-400"
                                >
                                    <div
                                        class="text-base text-gray-900 dark:text-white"
                                    >
                                        {{ role.title }}
                                    </div>
                                </td>

                                <td
                                    class="max-w-sm px-2 py-4 overflow-hidden text-base font-normal text-gray-500 truncate xl:max-w-xs dark:text-gray-400"
                                >
                                    <div
                                        class="text-base text-gray-900 dark:text-white"
                                    >
                                        {{ role.location }}
                                    </div>
                                </td>

                                <td
                                    class="px-2 py-4 text-base font-normal text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    <div
                                        v-if="role.status === 1"
                                        class="flex items-center"
                                    >
                                        <div
                                            class="h-2.5 w-2.5 rounded-full bg-gray-400 mr-2"
                                        ></div>
                                        Pending
                                    </div>

                                    <div
                                        v-else-if="role.status === 2"
                                        class="flex items-center"
                                    >
                                        <div
                                            class="h-2.5 w-2.5 rounded-full bg-yellow-400 mr-2"
                                        ></div>
                                        For Interview
                                    </div>

                                    <div
                                        v-else-if="role.status === 3"
                                        class="flex items-center"
                                    >
                                        <div
                                            class="h-2.5 w-2.5 rounded-full bg-orange-400 mr-2"
                                        ></div>
                                        In Progress
                                    </div>

                                    <div
                                        v-else-if="role.status === 4"
                                        class="flex items-center"
                                    >
                                        <div
                                            class="h-2.5 w-2.5 rounded-full bg-blue-400 mr-2"
                                        ></div>
                                        Qualified
                                    </div>

                                    <div
                                        v-else-if="role.status === 5"
                                        class="flex items-center"
                                    >
                                        <div
                                            class="h-2.5 w-2.5 rounded-full bg-green-400 mr-2"
                                        ></div>
                                        Hired
                                    </div>

                                    <div v-else class="flex items-center">
                                        <div
                                            class="h-2.5 w-2.5 rounded-full bg-red-500 mr-2"
                                        ></div>
                                        Not Qualified
                                    </div>
                                </td>

                                <td
                                    class="px-2 py-4 space-x-2 whitespace-nowrap"
                                >
                                    <button
                                        @click="showDisapproveModal(role.id)"
                                        type="button"
                                        id="disapproveUserButton"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-red-700 rounded-lg hover:bg-red-800 focus:ring-4 focus:ring-red-300 dark:focus:ring-red-900"
                                    >
                                        Disapprove
                                    </button>

                                    <button
                                        @click="showApproveModal(role.id)"
                                        type="button"
                                        id="approveUserButton"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-green-700 rounded-lg hover:bg-green-800 focus:ring-4 focus:ring-green-300 dark:focus:ring-green-900"
                                    >
                                        Approve
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="roles.data.length === 0">
                                <td
                                    colspan="11"
                                    class="max-w-sm text-center p-4 overflow-hidden text-base font-normal text-gray-500 truncate xl:max-w-xs dark:text-gray-400"
                                >
                                    <div
                                        class="text-base text-gray-900 dark:text-white"
                                    >
                                        No {{ title }} found.
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div
        class="sticky bottom-0 right-0 items-center w-full p-4 bg-white border-t border-gray-200 sm:flex sm:justify-between dark:bg-gray-800 dark:border-gray-700"
    >
        <Pagination :roles="roles" :pagination="pagination" />
    </div>

    <!-- Options Modal Modal -->
    <Transition
        enter-from-class="translate-x-full"
        enter-active-class="transition-transform translate-x-0"
        leave-active-class="transition-transform translate-x-0"
        leave-to-class="translate-x-full"
    >
        <div
            v-if="viewInfoModalVisibility"
            id="drawer-delete-product-default"
            class="fixed top-0 right-0 z-40 w-full h-screen max-w-xs p-4 overflow-y-auto bg-white dark:bg-gray-800"
            tabindex="-1"
            aria-labelledby="drawer-label"
            aria-hidden="true"
        >
            <h5
                id="drawer-label"
                class="inline-flex items-center text-sm font-semibold text-gray-500 uppercase dark:text-gray-400"
            >
                {{ title }} Information
            </h5>
            <button
                @click="hideInfoModal"
                type="button"
                aria-controls="drawer-delete-product-default"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 absolute top-2.5 right-2.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white"
            >
                <svg
                    aria-hidden="true"
                    class="w-5 h-5"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                    ></path>
                </svg>
                <span class="sr-only">Close menu</span>
            </button>

            <div class="px-4 py-8 space-y-4">
                <div>
                    <h3 class="text-md text-gray-500 dark:text-white">
                        <span class="font-bold text-black dark:text-gray-400"
                            >First Name:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{ form.first_name }}
                    </p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400"
                            >Middle Name:
                        </span>
                    </h3>
                    <span v-if="form.middle_name">{{ form.middle_name }}</span>
                    <p class="text-gray-500 dark:text-gray-600" v-else>N/A</p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400"
                            >Last Name:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{ form.last_name }}
                    </p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400"
                            >Gender:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">{{ form.gender }}</p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400"
                            >Email Address:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">{{ form.email }}</p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400"
                            >Contact Number:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{ form.contact_number }}
                    </p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400"
                            >Job ID:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{ form.job_id }}
                    </p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400"
                            >Job Title:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{ form.job_title }}
                    </p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400"
                            >Location:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{ form.location }}
                    </p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400"
                            >Resume File Name:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{ form.resume_name }}
                    </p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400"
                            >Resume File Path:
                        </span>
                    </h3>
                    <p class="break-words">
                        <a
                            target="_blank"
                            :href="form.resume_file"
                            class="text-blue-600 hover:text-blue-700 whitespace-normal dark:text-blue-500 dark:hover:text-blue-600"
                        >
                            {{ form.resume_file }}
                        </a>
                    </p>
                </div>
            </div>

            <div class="flex justify-center w-full py-4 space-x-4">
                <button
                    @click="showApproveModal(currentUpdatingUserID)"
                    class="text-white w-full justify-center bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800 disabled:bg-green-200 dark:disabled:bg-green-900"
                >
                    Approve
                </button>

                <button
                    @click="showDisapproveModal(currentUpdatingUserID)"
                    type="button"
                    id="deleteJobsButton"
                    class="inline-flex w-full justify-center text-white items-center bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 rounded-lg text-sm font-medium px-5 py-2.5 focus:z-10 dark:bg-red-700 dark:hover:bg-red-900 dark:focus:ring-red-900"
                >
                    Disapprove
                </button>
            </div>
        </div>
    </Transition>

    <!-- Disapprove Product Drawer -->
    <Transition
        enter-from-class="translate-x-full"
        enter-active-class="transition-transform translate-x-0"
        leave-active-class="transition-transform translate-x-0"
        leave-to-class="translate-x-full"
    >
        <div
            v-if="disapproveModalVisibility"
            id="drawer-delete-product-default"
            class="fixed top-0 right-0 z-40 w-full h-screen max-w-xs p-4 overflow-y-auto bg-white dark:bg-gray-800"
            tabindex="-1"
            aria-labelledby="drawer-label"
            aria-hidden="true"
        >
            <h5
                id="drawer-label"
                class="inline-flex items-center text-sm font-semibold text-gray-500 uppercase dark:text-gray-400"
            >
                Disapprove
            </h5>
            <button
                @click="hideDisapproveModal"
                type="button"
                aria-controls="drawer-delete-product-default"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 absolute top-2.5 right-2.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white"
            >
                <svg
                    aria-hidden="true"
                    class="w-5 h-5"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                    ></path>
                </svg>
                <span class="sr-only">Close menu</span>
            </button>
            <svg
                class="w-10 h-10 mt-8 mb-4 text-red-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                ></path>
            </svg>
            <h3 class="mb-6 text-lg text-gray-500 dark:text-gray-400">
                Are you sure you want to disapprove this {{ title }}?
            </h3>

            <form @submit.prevent="disapprove" class="inline-block">
                <button
                    type="submit"
                    class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm inline-flex items-center px-3 py-2.5 text-center mr-2 dark:focus:ring-red-900"
                >
                    Yes, I'm sure
                </button>
            </form>
            <button
                @click="hideDisapproveModal"
                class="text-gray-900 bg-white hover:bg-gray-100 focus:ring-4 focus:ring-blue-300 border border-gray-200 font-medium inline-flex items-center rounded-lg text-sm px-3 py-2.5 text-center dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-gray-700"
                data-modal-toggle="delete-product-modal"
            >
                No, cancel
            </button>
        </div>
    </Transition>

    <!-- Approve Product Drawer -->
    <Transition
        enter-from-class="translate-x-full"
        enter-active-class="transition-transform translate-x-0"
        leave-active-class="transition-transform translate-x-0"
        leave-to-class="translate-x-full"
    >
        <div
            v-if="approveModalVisibility"
            id="drawer-delete-product-default"
            class="fixed top-0 right-0 z-40 w-full h-screen max-w-xs p-4 overflow-y-auto bg-white dark:bg-gray-800"
            tabindex="-1"
            aria-labelledby="drawer-label"
            aria-hidden="true"
        >
            <h5
                id="drawer-label"
                class="inline-flex items-center text-sm font-semibold text-gray-500 uppercase dark:text-gray-400"
            >
                Approve
            </h5>
            <button
                @click="hideApproveModal"
                type="button"
                aria-controls="drawer-delete-product-default"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 absolute top-2.5 right-2.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white"
            >
                <svg
                    aria-hidden="true"
                    class="w-5 h-5"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path
                        fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                    ></path>
                </svg>
                <span class="sr-only">Close menu</span>
            </button>
            <svg
                class="w-10 h-10 mt-8 mb-4 text-green-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                ></path>
            </svg>
            <h3 class="mb-6 text-lg text-gray-500 dark:text-gray-400">
                Are you sure you want to approve this {{ title }}?
            </h3>
            <form @submit.prevent="approve" class="inline-block">
                <button
                    type="submit"
                    class="text-white bg-green-600 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm inline-flex items-center px-3 py-2.5 text-center mr-2 dark:focus:ring-green-900"
                >
                    Yes, I'm sure
                </button>
            </form>
            <button
                @click="hideApproveModal"
                class="text-gray-900 bg-white hover:bg-gray-100 focus:ring-4 focus:ring-blue-300 border border-gray-200 font-medium inline-flex items-center rounded-lg text-sm px-3 py-2.5 text-center dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-gray-700"
            >
                No, cancel
            </button>
        </div>
    </Transition>
</template>
