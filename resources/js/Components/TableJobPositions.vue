<script setup>
import { router, usePage, useForm } from "@inertiajs/vue3";
import {
    ref,
    computed,
    watch,
    Transition,
    Teleport,
    provide,
    reactive,
} from "vue";
import debounce from "lodash.debounce";
import { useToast } from "vue-toastification";
import Pagination from "../Partials/Table/Pagination.vue";
import InputField from "./InputField.vue";
import TextArea from "./TextArea.vue";
import SelectInput from "./SelectInput.vue";
import TextEditorModal from "./TextEditorModal.vue";
import { QuillEditor } from "@vueup/vue-quill";
import "@vueup/vue-quill/dist/vue-quill.snow.css";
import "@vueup/vue-quill/dist/vue-quill.bubble.css";

const page = usePage();
const toast = useToast();

const props = defineProps({
    roles: Object,
    pagination: Object,
    filters: Object,
    companyAssignments: Array,
    jobTypes: Array,
    employeeTypes: Array,
    industries: Array,
    linkName: String,
    title: String,
});

const form = useForm({
    title: "",
    description: "",
    company_profile: null,
    location: "",
    job_type_id: "",
    employee_type_id: "",
    industry_id: "",
    schedule: "",
});

const addModalForm = reactive({
    title: "",
    description: "",
    company_profile: null,
    location: "",
    job_type_id: "",
    employee_type_id: "",
    industry_id: "",
    schedule: "",
});

const richTextEditorOptions = ref({
    debug: "info",
    placeholder: "Please edit in full screen.",
    readOnly: true,
    theme: "snow",
});

const richTextEditorModal = ref(false);

let search = ref(props.filters.search);
let employeeTypesList = ref(null);

let currentUpdatingJobID = ref(null);
let currentJobIsActive = ref(null);

let updateModalVisibility = ref(false);
let addModalVisibility = ref(false);
let viewInfoModalVisibility = ref(false);
let activationModalVisibility = ref(false);
let deactivationModalVisibility = ref(false);

const submit = () => {
    form.post(`/${page.props.user.role}/${props.linkName}/store`, {
        onSuccess: () => {
            toast.success(`${props.title} added successfully!`);
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
        `/${page.props.user.role}/${props.linkName}/update/${currentUpdatingJobID.value}`,
        {
            onSuccess: () => {
                toast.success(`${props.title} updated successfully!`);
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
        `/${page.props.user.role}/${props.linkName}/activate/${currentUpdatingJobID.value}`,
        {
            onSuccess: () => {
                toast.success(`${props.title} activated successfully!`);
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
        `/${page.props.user.role}/${props.linkName}/deactivate/${currentUpdatingJobID.value}`,
        {
            onSuccess: () => {
                toast.success(`${props.title} deactivated successfully!`);
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

    currentUpdatingJobID.value = id;

    activationModalVisibility.value = true;
};

const hideActivationModal = () => {
    document.body.classList.remove("overflow-hidden");

    currentUpdatingJobID.value = null;

    viewInfoModalVisibility.value = false;
    activationModalVisibility.value = false;
};

// Deactivation Modal
const showDeactivationModal = (id) => {
    document.body.classList.remove("overflow-hidden");
    viewInfoModalVisibility.value = false;

    document.body.classList.add("overflow-hidden");

    currentUpdatingJobID.value = id;

    deactivationModalVisibility.value = true;
};

const hideDeactivationModal = () => {
    document.body.classList.remove("overflow-hidden");

    currentUpdatingJobID.value = null;

    viewInfoModalVisibility.value = false;
    deactivationModalVisibility.value = false;
};

const showRichTextModal = () => {
    richTextEditorModal.value = true;
};

const hideRichTextModal = () => {
    richTextEditorModal.value = false;
};

provide("hideRichTextModal", hideRichTextModal);

// Show Info Modal
const showInfoModal = (data) => {
    form.title = data.title;
    form.description = data.description;
    form.company_profile = data.company_profile;
    form.location = data.location;
    form.job_type_id = data.job_type_id;
    form.employee_type_id = data.employee_type_id;
    form.industry_id = data.industry_id;
    form.schedule = data.schedule;

    document.body.classList.add("overflow-hidden");

    currentUpdatingJobID.value = data.id;
    currentJobIsActive.value = data.is_active;

    viewInfoModalVisibility.value = true;
};

const hideInfoModal = () => {
    document.body.classList.remove("overflow-hidden");

    currentUpdatingJobID.value = null;
    currentJobIsActive.value = null;

    viewInfoModalVisibility.value = false;

    form.reset();
    form.clearErrors();
};

// Update Modal
const showUpdateModal = (data) => {
    document.body.classList.remove("overflow-hidden");
    viewInfoModalVisibility.value = false;

    if (data) {
        form.title = data.title;
        form.description = data.description;
        form.company_profile = data.company_profile;
        form.location = data.location;
        form.job_type_id = data.job_type_id;
        form.employee_type_id = data.employee_type_id;
        form.industry_id = data.industry_id;
        form.schedule = data.schedule;

        if(!currentUpdatingJobID.value) {
            currentUpdatingJobID.value = data.id;
        }
    }

    document.body.classList.add("overflow-hidden");

    updateModalVisibility.value = true;
};

const hideUpdateModal = () => {
    document.body.classList.remove("overflow-hidden");

    currentUpdatingJobID.value = null;

    updateModalVisibility.value = false;
    viewInfoModalVisibility.value = false;

    form.reset();
    form.clearErrors();
};

const showAddModal = () => {
    form.title = addModalForm.title;
    form.description = addModalForm.description;
    form.company_profile = addModalForm.company_profile;
    form.location = addModalForm.location;
    form.job_type_id = addModalForm.job_type_id;
    form.employee_type_id = addModalForm.employee_type_id;
    form.industry_id = addModalForm.industry_id;
    form.schedule = addModalForm.schedule;

    document.body.classList.add("overflow-hidden");

    addModalVisibility.value = true;
};

const hideAddModal = () => {
    document.body.classList.remove("overflow-hidden");

    addModalVisibility.value = false;

    addModalForm.title = form.title;
    addModalForm.description = form.description;
    addModalForm.company_profile = form.company_profile;
    addModalForm.location = form.location;
    addModalForm.job_type_id = form.job_type_id;
    addModalForm.employee_type_id = form.employee_type_id;
    addModalForm.industry_id = form.industry_id;
    addModalForm.schedule = form.schedule;

    form.reset();
    form.clearErrors();
};

const updateEmployeeTypes = computed(() => {
    return props.employeeTypes.filter(
        (val, index) => val.job_type_id == form.job_type_id
    );
});

const findJobType = (job_type_id) => {
    const findJobTypeId = props.jobTypes.find(el => el.id == job_type_id);

    return findJobTypeId.title
};

const findEmployeeType = (employee_type_id) => {
    const findEmployeeTypeId = props.employeeTypes.find(el => el.id == employee_type_id);

    return findEmployeeTypeId.title
};

const findIndustry = (industry_id) => {
    const findIndustryId = props.industries.find(el => el.id == industry_id);

    return findIndustryId.title
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

watch(
    () => form.job_type_id,
    (value) => {
        employeeTypesList = props.employeeTypes.filter(
            (val, index) => val.job_type_id == value
        );
    }
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

            <TextEditorModal v-if="richTextEditorModal">
                <QuillEditor
                    v-model:content="form.company_profile"
                    contentType="html"
                    theme="snow"
                />
            </TextEditorModal>
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
                <button
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
                                    Title
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
                                    Job Type
                                </th>

                                <th
                                    scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Employment Type
                                </th>

                                <th
                                    scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Industry
                                </th>

                                <th
                                    scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Schedule
                                </th>

                                <th
                                    scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Status
                                </th>

                                <th
                                    scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400"
                                >
                                    Created At
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
                                        {{ role.title }}
                                    </div>
                                </td>

                                <td
                                    class="px-2 py-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    <div
                                        class="text-base max-w-xs whitespace-normal text-gray-900 dark:text-white"
                                    >
                                        {{ role.location }}
                                    </div>
                                </td>

                                <td
                                    class="px-2 py-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    <div
                                        class="text-base max-w-xs whitespace-normal text-gray-900 dark:text-white"
                                    >
                                        {{
                                             findJobType(role.job_type_id)
                                        }}
                                    </div>
                                </td>

                                <td
                                    class="px-2 py-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    <div
                                        class="text-base max-w-xs whitespace-normal text-gray-900 dark:text-white"
                                    >
                                        {{
                                            findEmployeeType(role.employee_type_id)
                                        }}
                                    </div>
                                </td>

                                <td
                                    class="px-2 py-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    <div
                                        class="text-base max-w-xs whitespace-normal text-gray-900 dark:text-white"
                                    >
                                        {{ findIndustry(role.industry_id) }}
                                    </div>
                                </td>

                                <td
                                    class="px-2 py-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    <div
                                        class="text-base max-w-xs whitespace-normal text-gray-900 dark:text-white"
                                    >
                                        {{ role.schedule }}
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
                                    class="px-2 py-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    <div
                                        class="text-base max-w-xs whitespace-normal text-gray-900 dark:text-white"
                                    >
                                        {{
                                            new Date(
                                                role.created_at
                                            ).toLocaleDateString("en-US", {
                                                month: "long",
                                                day: "numeric",
                                                year: "numeric",
                                            })
                                        }}
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
                            >Title:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{ form.title }}
                    </p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400"
                            >Description:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{ form.description }}
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
                            >Job Type:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{ findJobType(form.job_type_id) }}
                    </p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400"
                            >Employment Type:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{
                            findEmployeeType(form.employee_type_id)
                        }}
                    </p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400"
                            >Industry:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{ findIndustry(form.industry_id) }}
                    </p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400"
                            >Schedule:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{ form.schedule }}
                    </p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400"
                            >Status:
                        </span>
                    </h3>
                    <p
                        class="text-black dark:text-white"
                        v-if="currentJobIsActive"
                    >
                        Active
                    </p>
                    <p class="text-black dark:text-white" v-else>Inactive</p>
                </div>
            </div>

            <div class="flex justify-center w-full py-4 space-x-4">
                <button
                    @click="showUpdateModal(form)"
                    class="text-white w-full justify-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 disabled:bg-blue-200 dark:disabled:bg-blue-900"
                >
                    Update
                </button>

                <button
                    v-if="currentJobIsActive"
                    @click="showDeactivationModal(currentUpdatingJobID)"
                    type="button"
                    id="deactivateUserButton"
                    class="inline-flex w-full justify-center text-white items-center bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 rounded-lg text-sm font-medium px-5 py-2.5 focus:z-10 dark:bg-red-700 dark:hover:bg-red-900 dark:focus:ring-red-900"
                >
                    Deactivate
                </button>

                <button
                    v-else
                    @click="showActivationModal(currentUpdatingJobID)"
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
                            id="title"
                            v-model="form.title"
                            type="text"
                            label="Title"
                            placeholder="Title"
                            :error="form.errors.title"
                        />
                    </div>

                    <div>
                        <TextArea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            label="Description"
                            placeholder="Description"
                            :error="form.errors.description"
                        />
                    </div>

                    <div>
                        <h1 class="text-gray-500">Company Profile</h1>
                        <QuillEditor :options="richTextEditorOptions" />

                        <button
                            type="button"
                            @click="showRichTextModal"
                            class="uppercase bg-gray-300 hover:bg-gray-400 duration-200 transition px-6 py-2 w-full"
                        >
                            Edit in Full Screen
                        </button>
                    </div>

                    <div>
                        <SelectInput
                            id="location"
                            v-model="form.location"
                            label="Location"
                            :error="form.errors.location"
                            :canSearch="false"
                        >
                            <option value="" disabled selected hidden></option>

                            <option
                                v-for="companyAssignment in props.companyAssignments.filter(
                                    (company) => company.is_active
                                )"
                                :key="companyAssignment.id"
                                :value="companyAssignment.title"
                            >
                                {{ companyAssignment.title }}
                            </option>
                        </SelectInput>
                    </div>

                    <div>
                        <SelectInput
                            id="job_type_id"
                            v-model="form.job_type_id"
                            label="Job Type"
                            :error="form.errors.job_type_id"
                            :canSearch="false"
                        >
                            <option value="" disabled selected hidden></option>

                            <option
                                v-for="jobType in props.jobTypes.filter(
                                    (job) => job.is_active
                                )"
                                :key="jobType.id"
                                :value="jobType.id"
                            >
                                {{ jobType.title }}
                            </option>
                        </SelectInput>
                    </div>

                    <div>
                        <SelectInput
                            id="employee_type_id"
                            v-model="form.employee_type_id"
                            label="Employment Type"
                            :error="form.errors.employee_type_id"
                            :canSearch="false"
                            :disabled="!Boolean(updateEmployeeTypes)"
                        >
                            <option value="" disabled selected hidden></option>

                            <option
                                v-for="employeeType in updateEmployeeTypes.filter(
                                    (employee) => employee.is_active
                                )"
                                :key="employeeType.id"
                                :value="employeeType.id"
                            >
                                {{ employeeType.title }}
                            </option>
                        </SelectInput>
                    </div>

                    <div>
                        <SelectInput
                            id="industry_id"
                            v-model="form.industry_id"
                            label="Industry"
                            :error="form.errors.industry_id"
                            :canSearch="false"
                        >
                            <option value="" disabled selected hidden></option>

                            <option
                                v-for="industry in props.industries.filter(
                                    (industry) => industry.is_active
                                )"
                                :key="industry.id"
                                :value="industry.id"
                            >
                                {{ industry.title }}
                            </option>
                        </SelectInput>
                    </div>

                    <div>
                        <InputField
                            id="schedule"
                            v-model="form.schedule"
                            type="text"
                            label="Schedule"
                            placeholder="Schedule"
                            :error="form.errors.schedule"
                        />
                    </div>
                </div>
                <div class="flex justify-center w-full py-4 space-x-4">
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
                            id="title"
                            v-model="form.title"
                            type="text"
                            label="Title"
                            placeholder="Title"
                            :error="form.errors.title"
                        />
                    </div>

                    <div>
                        <TextArea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            label="Description"
                            placeholder="Description"
                            :error="form.errors.description"
                        />
                    </div>

                    <div>
                        <h1 class="text-gray-500">Company Profile</h1>
                        <QuillEditor :options="richTextEditorOptions" />

                        <button
                            type="button"
                            @click="showRichTextModal"
                            class="uppercase bg-gray-300 hover:bg-gray-400 duration-200 transition px-6 py-2 w-full"
                        >
                            Edit in Full Screen
                        </button>
                    </div>

                    <div>
                        <SelectInput
                            id="location"
                            v-model="form.location"
                            label="Location"
                            :error="form.errors.location"
                            :canSearch="false"
                        >
                            <option value="" disabled selected hidden></option>

                            <option
                                v-for="companyAssignment in props.companyAssignments.filter(
                                    (company) => company.is_active
                                )"
                                :key="companyAssignment.id"
                                :value="companyAssignment.title"
                            >
                                {{ companyAssignment.title }}
                            </option>
                        </SelectInput>
                    </div>

                    <div>
                        <SelectInput
                            id="job_type_id"
                            v-model="form.job_type_id"
                            label="Job Type"
                            :error="form.errors.job_type_id"
                            :canSearch="false"
                        >
                            <option value="" disabled selected hidden></option>

                            <option
                                v-for="jobType in props.jobTypes.filter(
                                    (job) => job.is_active
                                )"
                                :key="jobType.id"
                                :value="jobType.id"
                            >
                                {{ jobType.title }}
                            </option>
                        </SelectInput>
                    </div>

                    <div>
                        <SelectInput
                            id="employee_type_id"
                            v-model="form.employee_type_id"
                            label="Employment Type"
                            :error="form.errors.employee_type_id"
                            :canSearch="false"
                            :disabled="!Boolean(employeeTypesList)"
                        >
                            <option value="" disabled selected hidden></option>

                            <option
                                v-for="employeeType in employeeTypesList?.filter(
                                    (employee) => employee.is_active
                                )"
                                :key="employeeType.id"
                                :value="employeeType.id"
                            >
                                {{ employeeType.title }}
                            </option>
                        </SelectInput>
                    </div>

                    <div>
                        <SelectInput
                            id="industry_id"
                            v-model="form.industry_id"
                            label="Industry"
                            :error="form.errors.industry_id"
                            :canSearch="false"
                        >
                            <option value="" disabled selected hidden></option>

                            <option
                                v-for="industry in props.industries.filter(
                                    (industry) => industry.is_active
                                )"
                                :key="industry.id"
                                :value="industry.id"
                            >
                                {{ industry.title }}
                            </option>
                        </SelectInput>
                    </div>

                    <div>
                        <InputField
                            id="schedule"
                            v-model="form.schedule"
                            type="text"
                            label="Schedule"
                            placeholder="Schedule"
                            :error="form.errors.schedule"
                        />
                    </div>
                </div>
                <div class="flex justify-center w-full py-4 space-x-4">
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
