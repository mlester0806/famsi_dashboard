<script setup>
import { router, usePage, useForm } from "@inertiajs/vue3";
import { ref, watch, Transition, Teleport, reactive } from "vue";
import debounce from "lodash.debounce";
import { useToast } from "vue-toastification";
import Pagination from "../Partials/Table/Pagination.vue";
import InputField from "./InputField.vue";
import SelectInput from "./SelectInput.vue";

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
});

const addModalForm = reactive({
    first_name: "",
    middle_name: "",
    last_name: "",
    gender: "",
    email: "",
    contact_number: "",
});

const page = usePage();

const toast = useToast();

let search = ref(props.filters.search);
let filterStatus = ref(props.filters.filterStatus);
let alreadyApplied = ref(props.filters.application);
let dateHired = ref(props.filters.date);

let currentUpdatingUserID = ref(null);
let currentUserIsActive = ref(null);
let currentUserEmailIsVerified = ref(null);

let updateModalVisibility = ref(false);
let addModalVisibility = ref(false);
let viewInfoModalVisibility = ref(false);
let activationModalVisibility = ref(false);
let deactivationModalVisibility = ref(false);

const submit = () => {
    form.post(`/${page.props.user.role}/${props.linkName}/store`, {
        onSuccess: () => {
            toast.success("User created successfully!");
            document.body.classList.remove("overflow-hidden");
            updateModalVisibility.value = false;
            addModalVisibility.value = false;
            form.reset();
            clearErrors();
        },
    });
};

const update = () => {
    form.put(
        `/${page.props.user.role}/${props.linkName}/update/${currentUpdatingUserID.value}`,
        {
            onSuccess: () => {
                toast.success("User updated successfully!");
                hideUpdateModal();
                hideAddModal();
                form.reset();
                clearErrors();
            },
        }
    );
};

const activate = () => {
    form.put(
        `/${page.props.user.role}/${props.linkName}/activate/${currentUpdatingUserID.value}`,
        {
            onSuccess: () => {
                toast.success("User activated successfully!");
                hideUpdateModal();
                hideAddModal();
                hideActivationModal();
                hideDeactivationModal();
                form.reset();
                clearErrors();
            },
        }
    );
};

const deactivate = () => {
    form.put(
        `/${page.props.user.role}/${props.linkName}/deactivate/${currentUpdatingUserID.value}`,
        {
            onSuccess: () => {
                toast.success("User deactivated successfully!");
                hideUpdateModal();
                hideAddModal();
                hideActivationModal();
                hideDeactivationModal();
                form.reset();
                clearErrors();
            },
        }
    );
};

// Activation Modal
const showActivationModal = (id) => {
    document.body.classList.remove("overflow-hidden");
    viewInfoModalVisibility.value = false;

    document.body.classList.add("overflow-hidden");

    currentUpdatingUserID.value = id;

    activationModalVisibility.value = true;
};

const hideActivationModal = () => {
    document.body.classList.remove("overflow-hidden");

    currentUpdatingUserID.value = null;

    viewInfoModalVisibility.value = false;
    activationModalVisibility.value = false;
};

// Deactivation Modal
const showDeactivationModal = (id) => {
    document.body.classList.remove("overflow-hidden");
    viewInfoModalVisibility.value = false;

    document.body.classList.add("overflow-hidden");

    currentUpdatingUserID.value = id;

    deactivationModalVisibility.value = true;
};

const hideDeactivationModal = () => {
    document.body.classList.remove("overflow-hidden");

    currentUpdatingUserID.value = null;

    viewInfoModalVisibility.value = false;
    deactivationModalVisibility.value = false;
};

// Show Info Modal
const showInfoModal = (data) => {
    form.first_name = data.first_name;
    form.middle_name = data.middle_name;
    form.last_name = data.last_name;
    form.gender = data.gender;
    form.email = data.email;
    form.contact_number = data.contact_number;

    document.body.classList.add("overflow-hidden");

    currentUpdatingUserID.value = data.id;
    currentUserIsActive.value = data.is_active;
    currentUserEmailIsVerified.value = data.email_verified_at;

    viewInfoModalVisibility.value = true;
};

const hideInfoModal = () => {
    document.body.classList.remove("overflow-hidden");

    currentUpdatingUserID.value = null;
    currentUserIsActive.value = null;
    currentUserEmailIsVerified.value = null;

    viewInfoModalVisibility.value = false;

    form.reset();

    form.clearErrors();
};

// Update Modal
const showUpdateModal = (data) => {
    document.body.classList.remove("overflow-hidden");
    viewInfoModalVisibility.value = false;

    if (data) {
        form.first_name = data.first_name;
        form.middle_name = data.middle_name;
        form.last_name = data.last_name;
        form.gender = data.gender;
        form.email = data.email;
        form.contact_number = data.contact_number;

        if (!currentUpdatingUserID.value) currentUpdatingUserID.value = data.id;
    }

    document.body.classList.add("overflow-hidden");

    updateModalVisibility.value = true;
};

const hideUpdateModal = () => {
    document.body.classList.remove("overflow-hidden");

    currentUpdatingUserID.value = null;

    viewInfoModalVisibility.value = false;
    updateModalVisibility.value = false;

    form.reset();

    form.clearErrors();
};

const showAddModal = () => {
    form.first_name = addModalForm.first_name;
    form.middle_name = addModalForm.middle_name;
    form.last_name = addModalForm.last_name;
    form.gender = addModalForm.gender;
    form.email = addModalForm.email;
    form.contact_number = addModalForm.contact_number;

    document.body.classList.add("overflow-hidden");

    addModalVisibility.value = true;
};

const hideAddModal = () => {
    document.body.classList.remove("overflow-hidden");

    addModalVisibility.value = false;

    addModalForm.first_name = form.first_name;
    addModalForm.middle_name = form.middle_name;
    addModalForm.last_name = form.last_name;
    addModalForm.gender = form.gender;
    addModalForm.email = form.email;
    addModalForm.contact_number = form.contact_number;

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

        if(filterStatus.value) {
            query.status = filterStatus.value;
        }

        if(alreadyApplied.value) {
            query.application = alreadyApplied.value;
        }

        if (dateHired.value) {
            query.date = dateHired.value;
        }

        router.get(`/${page.props.user.role}/${props.linkName}`, query, {
            preserveState: true,
            replace: true,
        });
    }, 500)
);

watch(
    filterStatus,
    debounce((value) => {
        const query = {};
        if (value) {
            query.status = value;
        }

        if (search.value) {
            query.search = search.value;
        }

        if(alreadyApplied.value) {
            query.application = alreadyApplied.value;
        }

        if (dateHired.value) {
            query.date = dateHired.value;
        }

        router.get(`/${page.props.user.role}/${props.linkName}`, query, {
            preserveState: true,
            replace: true,
        });
    }, 500)
);

watch(
    alreadyApplied,
    debounce((value) => {
        const query = {};
        if (value) {
            query.application = value;
        }

        if (search.value) {
            query.search = search.value;
        }

        if (filterStatus.value) {
            query.status = filterStatus.value;
        }

        if (dateHired.value) {
            query.date = dateHired.value;
        }

        router.get(`/${page.props.user.role}/${props.linkName}`, query, {
            preserveState: true,
            replace: true,
        });
    }, 500)
);

watch(
    dateHired,
    debounce((value) => {
        const query = {};
        if (value) {
            query.date = value;
        }

        if (search.value) {
            query.search = search.value;
        }

        if (filterStatus.value) {
            query.status = filterStatus.value;
        }

        if (alreadyApplied.value) {
            query.application = alreadyApplied.value;
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
                v-if="updateModalVisibility"
                @click="hideUpdateModal"
                class="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-30 transition duration-200"
            ></div>

            <div
                v-else-if="addModalVisibility"
                @click="hideAddModal"
                class="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-30 transition duration-200"
            ></div>

            <div
                v-else-if="activationModalVisibility"
                @click="hideActivationModal"
                class="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-30 transition duration-200"
            ></div>

            <div
                v-else-if="deactivationModalVisibility"
                @click="hideDeactivationModal"
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
                <div class="flex items-center mb-4">
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
            <div
                class="items-center justify-between block sm:flex md:divide-x md:divide-gray-100 dark:divide-gray-700"
            >
            <div class="flex items-center mb-4 sm:mb-0">
                    <div class="relative w-48 sm:w-64">
                        <SelectInput
                            id="dateHired"
                            v-model="dateHired"
                            label="Select Date"
                            :canSearch="false"
                        >
                            <option value="" disabled selected hidden></option>

                            <option
                                value="All"
                                :selected="filterStatus !== 'Last Week' || filterStatus !== 'Last Month'"
                            >
                                All
                            </option>
                            <option
                                value="Last Week"
                                :selected="filterStatus === 'Last Week'"
                            >
                                Last Week
                            </option>
                            <option
                                value="Last Month"
                                :selected="filterStatus === 'Last Month'"
                            >
                                Last Month
                            </option>

                        </SelectInput>
                    </div>
                </div>
                <div class="flex items-center mb-4 sm:mb-0">
                    <div class="relative w-48 sm:w-64">
                        <SelectInput
                            id="alreadyApplied"
                            v-model="alreadyApplied"
                            label="Select Application"
                            :canSearch="false"
                        >
                            <option value="" disabled selected hidden></option>

                            <option
                                value="All"
                                :selected="filterStatus !== 'Not Yet Applied' || filterStatus !== 'Already Applied'"
                            >
                                All
                            </option>
                            <option
                                value="Not Yet Applied"
                                :selected="filterStatus === 'Not Yet Applied'"
                            >
                                Not Yet Applied
                            </option>
                            <option
                                value="Already Applied"
                                :selected="filterStatus === 'Already Applied'"
                            >
                                Already Applied
                            </option>

                        </SelectInput>
                    </div>
                </div>
                <div class="flex items-center mb-4 sm:mb-0">
                    <div class="relative w-48 sm:w-64">
                        <SelectInput
                            id="filterStatus"
                            v-model="filterStatus"
                            label="Select Status"
                            :canSearch="false"
                        >
                            <option value="" disabled selected hidden></option>

                            <option
                                value="All"
                                :selected="filterStatus !== 'Pending' || filterStatus !== 'For Interview' || filterStatus !== 'In Progress' || filterStatus !== 'Qualified' || filterStatus !== 'Disqualified' || filterStatus !== 'Hired'"
                            >
                                All
                            </option>

                            <option
                                value="Pending"
                                :selected="filterStatus === 'Pending'"
                            >
                                Pending
                            </option>
                            <option
                                value="For Interview"
                                :selected="filterStatus === 'For Interview'"
                            >
                                For Interview
                            </option>
                            <option
                                value="In Progress"
                                :selected="filterStatus === 'In Progress'"
                            >
                                In Progress
                            </option>
                            <option
                                value="Qualified"
                                :selected="filterStatus === 'Qualified'"
                            >
                                Qualified
                            </option>
                            <option
                                value="Disqualified"
                                :selected="filterStatus === 'Disqualified'"
                            >
                                Disqualified
                            </option>
                            <option
                                value="Hired"
                                :selected="filterStatus === 'Hired'"
                            >
                                Hired
                            </option>
                            <option
                                value="Resigned"
                                :selected="filterStatus === 'Resigned'"
                            >
                                Resigned
                            </option>
                        </SelectInput>
                    </div>
                </div>
                <button
                    v-if="page.props.user.role !== 'hr-manager'"
                    id="addNewButton"
                    @click="showAddModal"
                    class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800"
                    type="button"
                >
                    <font-awesome-icon
                        class="mr-2 -ml-1"
                        :icon="['fas', 'plus']"
                    />
                    Add new {{ title }}
                </button>
               
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
                                    Email Status
                                </th>

                                <th
                                    scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Account Status
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
                                    class="px-2 py-4 text-base font-normal text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    <div
                                        v-if="role.email_verified_at"
                                        class="flex items-center"
                                    >
                                        <div
                                            class="h-2.5 w-2.5 rounded-full bg-green-400 mr-2"
                                        ></div>
                                        Verified
                                    </div>

                                    <div v-else class="flex items-center">
                                        <div
                                            class="h-2.5 w-2.5 rounded-full bg-red-500 mr-2"
                                        ></div>
                                        Not Verified
                                    </div>
                                </td>

                                <td
                                    class="px-2 py-4 text-base font-normal text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    <div
                                        v-if="role.is_active"
                                        class="flex items-center"
                                    >
                                        <div
                                            class="h-2.5 w-2.5 rounded-full bg-green-400 mr-2"
                                        ></div>
                                        Active
                                    </div>

                                    <div v-else class="flex items-center">
                                        <div
                                            class="h-2.5 w-2.5 rounded-full bg-red-500 mr-2"
                                        ></div>
                                        Inactive
                                    </div>
                                </td>

                                <td
                                    class="px-2 py-4 space-x-2 whitespace-nowrap"
                                >
                                    <button
                                        type="button"
                                        id="updateProductButton"
                                        @click="
                                            showUpdateModal(roles.data[index])
                                        "
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white rounded-lg bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                                    >
                                        Update
                                    </button>
                                    <button
                                        v-if="role.is_active"
                                        @click="showDeactivationModal(role.id)"
                                        type="button"
                                        id="deactivateUserButton"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-red-700 rounded-lg hover:bg-red-800 focus:ring-4 focus:ring-red-300 dark:focus:ring-red-900"
                                    >
                                        Deactivate
                                    </button>

                                    <button
                                        v-else
                                        @click="showActivationModal(role.id)"
                                        type="button"
                                        id="activateUserButton"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-green-700 rounded-lg hover:bg-green-800 focus:ring-4 focus:ring-green-300 dark:focus:ring-green-900"
                                    >
                                        Activate
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="roles.data.length === 0">
                                <td
                                    colspan="9"
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
                            >Email Status:
                        </span>
                    </h3>
                    <p
                        class="text-black dark:text-white"
                        v-if="currentUserEmailIsVerified"
                    >
                        Verified
                    </p>
                    <p class="text-black dark:text-white" v-else>
                        Not Verified
                    </p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400"
                            >Account Status:
                        </span>
                    </h3>
                    <p
                        class="text-black dark:text-white"
                        v-if="currentUserIsActive"
                    >
                        Active
                    </p>
                    <p class="text-black dark:text-white" v-else>Inactive</p>
                </div>
            </div>

            <div
                class="bottom-0 left-0 flex justify-center w-full pb-4 space-x-4 md:px-4 absolute"
            >
                <button
                    @click="showUpdateModal(form)"
                    class="text-white w-full justify-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 disabled:bg-blue-200 dark:disabled:bg-blue-900"
                >
                    Update
                </button>

                <button
                    v-if="currentUserIsActive"
                    @click="showDeactivationModal(currentUpdatingUserID)"
                    type="button"
                    id="deactivateUserButton"
                    class="inline-flex w-full justify-center text-white items-center bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 rounded-lg text-sm font-medium px-5 py-2.5 focus:z-10 dark:bg-red-700 dark:hover:bg-red-900 dark:focus:ring-red-900"
                >
                    Deactivate
                </button>

                <button
                    v-else
                    @click="showActivationModal(currentUpdatingUserID)"
                    type="button"
                    id="activateUserButton"
                    class="inline-flex w-full justify-center text-white items-center bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 rounded-lg text-sm font-medium px-5 py-2.5 focus:z-10 dark:bg-green-700 dark:hover:bg-green-900 dark:focus:ring-green-900"
                >
                    Activate
                </button>
            </div>
        </div>
    </Transition>

    <!-- Update Modal -->
    <Transition
        enter-from-class="translate-x-full"
        enter-active-class="transition-transform translate-x-0"
        leave-active-class="transition-transform translate-x-0"
        leave-to-class="translate-x-full"
    >
        <div
            v-if="updateModalVisibility"
            id="drawer-update-product-default"
            class="fixed top-0 right-0 z-40 w-full h-screen max-w-xs p-4 overflow-y-auto bg-white dark:bg-gray-800"
            tabindex="-1"
            aria-labelledby="drawer-label"
            aria-hidden="true"
        >
            <h5
                id="drawer-label"
                class="inline-flex items-center mb-6 text-sm font-semibold text-gray-500 uppercase dark:text-gray-400"
            >
                Update {{ title }}
            </h5>
            <button
                type="button"
                @click="hideUpdateModal"
                aria-controls="drawer-update-product-default"
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
                <span class="sr-only">Close</span>
            </button>
            <form @submit.prevent="update">
                <div class="space-y-10">
                    <div>
                        <InputField
                            id="firstName"
                            v-model="form.first_name"
                            type="text"
                            label="First Name"
                            placeholder="First Name"
                            :error="form.errors.first_name"
                        />
                    </div>
                    <div>
                        <InputField
                            id="middleName"
                            v-model="form.middle_name"
                            type="text"
                            label="Middle Name"
                            placeholder="Middle Name"
                            :error="form.errors.middle_name"
                        />
                    </div>
                    <div>
                        <InputField
                            id="lastName"
                            v-model="form.last_name"
                            type="text"
                            label="Last Name"
                            placeholder="Last Name"
                            :error="form.errors.last_name"
                        />
                    </div>
                    <div>
                        <SelectInput
                            id="gender"
                            v-model="form.gender"
                            label="Gender"
                            :error="form.errors.gender"
                            :canSearch="false"
                        >
                            <option value="" disabled selected hidden></option>

                            <option
                                value="Male"
                                :selected="form.gender === 'Male'"
                            >
                                Male
                            </option>
                            <option
                                value="Female"
                                :selected="form.gender === 'Female'"
                            >
                                Female
                            </option>
                        </SelectInput>
                    </div>
                    <div>
                        <InputField
                            id="emailAddress"
                            v-model="form.email"
                            type="email"
                            label="Email Address"
                            placeholder="Email Address"
                            :error="form.errors.email"
                        />
                    </div>
                    <div>
                        <InputField
                            id="contactNumber"
                            v-model="form.contact_number"
                            type="contactNumber"
                            label="Contact Number"
                            placeholder="Contact Number"
                            :error="form.errors.contact_number"
                        />
                    </div>
                </div>
                <div
                    class="bottom-0 left-0 flex justify-center w-full pb-4 mt-4 space-x-4 sm:absolute sm:px-4 sm:mt-0"
                >
                    <button
                        type="submit"
                        class="w-full justify-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800"
                    >
                        Update
                    </button>
                    <button
                        @click="hideUpdateModal"
                        type="button"
                        aria-controls="drawer-create-product-default"
                        class="inline-flex w-full justify-center text-gray-500 items-center bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600"
                    >
                        <svg
                            aria-hidden="true"
                            class="w-5 h-5 -ml-1 sm:mr-1"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            ></path>
                        </svg>
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </Transition>

    <!-- Deactivation Product Drawer -->
    <Transition
        enter-from-class="translate-x-full"
        enter-active-class="transition-transform translate-x-0"
        leave-active-class="transition-transform translate-x-0"
        leave-to-class="translate-x-full"
    >
        <div
            v-if="deactivationModalVisibility"
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
                Deactivate
            </h5>
            <button
                @click="hideDeactivationModal"
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
                Are you sure you want to deactivate this {{ title }}?
            </h3>

            <form @submit.prevent="deactivate" class="inline-block">
                <button
                    type="submit"
                    class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm inline-flex items-center px-3 py-2.5 text-center mr-2 dark:focus:ring-red-900"
                >
                    Yes, I'm sure
                </button>
            </form>
            <button
                @click="hideDeactivationModal"
                class="text-gray-900 bg-white hover:bg-gray-100 focus:ring-4 focus:ring-blue-300 border border-gray-200 font-medium inline-flex items-center rounded-lg text-sm px-3 py-2.5 text-center dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-gray-700"
                data-modal-toggle="delete-product-modal"
            >
                No, cancel
            </button>
        </div>
    </Transition>

    <!-- Activation Product Drawer -->
    <Transition
        enter-from-class="translate-x-full"
        enter-active-class="transition-transform translate-x-0"
        leave-active-class="transition-transform translate-x-0"
        leave-to-class="translate-x-full"
    >
        <div
            v-if="activationModalVisibility"
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
                Activate
            </h5>
            <button
                @click="hideActivationModal"
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
                Are you sure you want to activate this {{ title }}?
            </h3>
            <form @submit.prevent="activate" class="inline-block">
                <button
                    type="submit"
                    class="text-white bg-green-600 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm inline-flex items-center px-3 py-2.5 text-center mr-2 dark:focus:ring-green-900"
                >
                    Yes, I'm sure
                </button>
            </form>
            <button
                @click="hideActivationModal"
                class="text-gray-900 bg-white hover:bg-gray-100 focus:ring-4 focus:ring-blue-300 border border-gray-200 font-medium inline-flex items-center rounded-lg text-sm px-3 py-2.5 text-center dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-gray-700"
            >
                No, cancel
            </button>
        </div>
    </Transition>

    <!-- Add Product Drawer -->
    <Transition
        enter-from-class="translate-x-full"
        enter-active-class="transition-transform translate-x-0"
        leave-active-class="transition-transform translate-x-0"
        leave-to-class="translate-x-full"
    >
        <div
            id="drawer-create-product-default"
            v-if="addModalVisibility"
            class="fixed top-0 right-0 z-40 w-full h-screen max-w-xs p-4 overflow-y-auto bg-white dark:bg-gray-800"
            tabindex="-1"
            aria-labelledby="drawer-label"
            aria-hidden="true"
        >
            <h5
                id="drawer-label"
                class="inline-flex items-center mb-6 text-sm font-semibold text-gray-500 uppercase dark:text-gray-400"
            >
                New {{ title }}
            </h5>
            <button
                type="button"
                @click="hideAddModal"
                aria-controls="drawer-create-product-default"
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
                <span class="sr-only">Close</span>
            </button>
            <form @submit.prevent="submit">
                <div class="space-y-10">
                    <div>
                        <InputField
                            id="firstName"
                            v-model="form.first_name"
                            type="text"
                            label="First Name"
                            placeholder="First Name"
                            :error="form.errors.first_name"
                        />
                    </div>
                    <div>
                        <InputField
                            id="middleName"
                            v-model="form.middle_name"
                            type="text"
                            label="Middle Name"
                            placeholder="Middle Name"
                            :error="form.errors.middle_name"
                        />
                    </div>
                    <div>
                        <InputField
                            id="lastName"
                            v-model="form.last_name"
                            type="text"
                            label="Last Name"
                            placeholder="Last Name"
                            :error="form.errors.last_name"
                        />
                    </div>
                    <div>
                        <SelectInput
                            id="gender"
                            v-model="form.gender"
                            label="Gender"
                            :error="form.errors.gender"
                            :canSearch="false"
                        >
                            <option value="" disabled selected hidden></option>

                            <option
                                value="Male"
                                :selected="form.gender === 'Male'"
                            >
                                Male
                            </option>
                            <option
                                value="Female"
                                :selected="form.gender === 'Female'"
                            >
                                Female
                            </option>
                        </SelectInput>
                    </div>
                    <div>
                        <InputField
                            id="emailAddress"
                            v-model="form.email"
                            type="email"
                            label="Email Address"
                            placeholder="Email Address"
                            :error="form.errors.email"
                        />
                    </div>
                    <div>
                        <InputField
                            id="contactNumber"
                            v-model="form.contact_number"
                            type="contactNumber"
                            label="Contact Number"
                            placeholder="Contact Number"
                            :error="form.errors.contact_number"
                        />
                    </div>
                </div>
                <div
                    class="bottom-0 left-0 flex justify-center w-full pb-4 space-x-4 md:px-4 md:absolute"
                >
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="text-white w-full justify-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 disabled:bg-blue-200 dark:disabled:bg-blue-900"
                    >
                        Add
                    </button>
                    <button
                        @click="hideAddModal"
                        type="button"
                        data-drawer-dismiss="drawer-create-product-default"
                        aria-controls="drawer-create-product-default"
                        class="inline-flex w-full justify-center text-gray-500 items-center bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600"
                    >
                        <svg
                            aria-hidden="true"
                            class="w-5 h-5 -ml-1 sm:mr-1"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            ></path>
                        </svg>
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </Transition>
</template>
