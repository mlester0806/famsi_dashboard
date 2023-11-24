<script setup>
import axios from "axios";
import { router, usePage, useForm } from "@inertiajs/vue3";
import { ref, computed, watch, Transition, Teleport, reactive } from "vue";
import debounce from "lodash.debounce";
import vueFilePond from "vue-filepond";
import { useToast } from "vue-toastification";
import Pagination from "../Partials/Table/Pagination.vue";
import InputField from "./InputField.vue";
import TextArea from "./TextArea.vue";

import "filepond/dist/filepond.min.css";
import "filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.css";
import FilePondPluginFileValidateType from "filepond-plugin-file-validate-type";
import FilePondPluginImagePreview from "filepond-plugin-image-preview";

const page = usePage();

const toast = useToast();

const form = useForm({
    title: "",
    description: "",
    filename: "",
    path: "",
});

const addModalForm = reactive({
    title: "",
    description: "",
    filename: "",
    path: "",
});

const isFileUploading = ref(false);

// Create component
const FilePond = vueFilePond(
    FilePondPluginFileValidateType,
    FilePondPluginImagePreview
);

const pond = ref(null);

// The `setup` function automatically exports everything you return
const handleFilePondInit = () => {
    console.log(pond);
};

const server = computed(() => ({
    url: "",
    process: {
        url: `/${page.props.user.role}/${props.linkName}/upload`,
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": page.props.csrf_token,
        },
        withCredentials: false,
        onload: (response) => {
            const res = JSON.parse(response);

            form.filename = res.filename;
            form.path = res.path;
            isFileUploading.value = false;
        },
        ondata: (formData) => {
            isFileUploading.value = true;
            return formData;
        },
    },
    remove: (source, load, error) => {
        form.filename = "";
        form.path = "";
        load();
    },
    revert: (uniqueId, load, error) => {
        axios.post(`/${page.props.user.role}/${props.linkName}/upload-revert`, {
            path: form.path,
        });

        form.filename = "";
        form.path = "";
        load();
    },
}));

const props = defineProps({
    roles: Object,
    pagination: Object,
    filters: Object,
    linkName: String,
    title: String,
});

let search = ref(props.filters.search);

let files = ref([]);

let currentUpdatingDocumentID = ref(null);

let updateModalVisibility = ref(false);
let addModalVisibility = ref(false);
let viewInfoModalVisibility = ref(false);
let deleteModalVisibility = ref(false);

const submit = () => {
    form.post(`/${page.props.user.role}/${props.linkName}/store`, {
        onSuccess: () => {
            toast.success("Document added successfully!");
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
        `/${page.props.user.role}/${props.linkName}/update/${currentUpdatingDocumentID.value}`,
        {
            onSuccess: () => {
                toast.success("Document updated successfully!");
                hideUpdateModal();
                hideAddModal();
                form.reset();
                clearErrors();
            },
        }
    );
};

const destroy = () => {
    form.delete(
        `/${page.props.user.role}/${props.linkName}/destroy/${currentUpdatingDocumentID.value}`,
        {
            onSuccess: () => {
                toast.success("Document deleted successfully!");
                hideUpdateModal();
                hideAddModal();
                hideDeleteModal();
                form.reset();
                clearErrors();
            },
        }
    );
};

// Delete Modal
const showDeleteModal = (id) => {
    document.body.classList.remove("overflow-hidden");
    viewInfoModalVisibility.value = false;

    document.body.classList.add("overflow-hidden");

    currentUpdatingDocumentID.value = id;

    deleteModalVisibility.value = true;
};

const hideDeleteModal = () => {
    document.body.classList.remove("overflow-hidden");

    currentUpdatingDocumentID.value = null;

    deleteModalVisibility.value = false;
    viewInfoModalVisibility.value = false;
};

// Show Info Modal
const showInfoModal = (data) => {
    form.title = data.title;
    form.description = data.description;
    form.filename = data.filename;
    form.path = data.path;

    document.body.classList.add("overflow-hidden");

    currentUpdatingDocumentID.value = data.id;

    viewInfoModalVisibility.value = true;
};

const hideInfoModal = () => {
    document.body.classList.remove("overflow-hidden");

    currentUpdatingDocumentID.value = null;

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
        form.filename = data.filename;
        form.path = data.path;

        if(!currentUpdatingDocumentID.value) {
            currentUpdatingDocumentID.value = data.id;
        }
    }

    document.body.classList.add("overflow-hidden");

    updateModalVisibility.value = true;
};

const hideUpdateModal = () => {
    document.body.classList.remove("overflow-hidden");

    currentUpdatingDocumentID.value = null;

    updateModalVisibility.value = false;
    viewInfoModalVisibility.value = false;

    form.reset();
    form.clearErrors();
};

const showAddModal = () => {
    form.title = addModalForm.title;
    form.description = addModalForm.description;
    form.filename = addModalForm.filename;
    form.path = addModalForm.path;

    document.body.classList.add("overflow-hidden");

    addModalVisibility.value = true;
};

const hideAddModal = () => {
    document.body.classList.remove("overflow-hidden");

    addModalVisibility.value = false;

    addModalForm.title = form.title;
    addModalForm.description = form.description;
    addModalForm.filename = form.filename;
    addModalForm.path = form.path;

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
                v-else-if="deleteModalVisibility"
                @click="hideDeleteModal"
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
                                    Description
                                </th>
                                <th
                                    scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400"
                                >
                                    File Name
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
                                        {{ role.description }}
                                    </div>
                                </td>

                                <td
                                    class="px-2 py-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white"
                                >
                                    <div
                                        class="text-base max-w-xs whitespace-normal text-gray-900 dark:text-white"
                                    >
                                        {{ role.filename }}
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
                                        @click="showDeleteModal(role.id)"
                                        type="button"
                                        id="deleteDocumentsButton"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-red-700 rounded-lg hover:bg-red-800 focus:ring-4 focus:ring-red-300 dark:focus:ring-red-900"
                                    >
                                        Delete
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
                            >File Name:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{ form.filename }}
                    </p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400"
                            >File Path:
                        </span>
                    </h3>
                    <p class="break-words">
                        <a
                            target="_blank"
                            :href="form.path"
                            class="text-blue-600 hover:text-blue-700 whitespace-normal dark:text-blue-500 dark:hover:text-blue-600"
                        >
                            {{ form.path }}
                        </a>
                    </p>
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
                    @click="showDeleteModal(currentUpdatingDocumentID)"
                    type="button"
                    id="deleteDocumentsButton"
                    class="inline-flex w-full justify-center text-white items-center bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 rounded-lg text-sm font-medium px-5 py-2.5 focus:z-10 dark:bg-red-700 dark:hover:bg-red-900 dark:focus:ring-red-900"
                >
                    Delete
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
                        <label
                            for="documentUpload"
                            class="text-gray-700 dark:text-neutral-200"
                            >File</label
                        >
                        <FilePond
                            name="documentUpload"
                            ref="pond"
                            label-idle="Upload file here or <span class='filepond--label-action'>Browse</span>"
                            :allow-drop="true"
                            accepted-file-types="application/pdf, application/docx"
                            :file="files"
                            v-model="form.path"
                            :server="server"
                            @init="handleFilePondInit"
                            credits="false"
                        />
                        <p
                            class="text-red-500 text-xs absolute -mt-2"
                            v-if="form.errors.filename"
                        >
                            {{ form.errors.filename }}
                        </p>
                    </div>
                </div>
                <div
                    class="bottom-0 left-0 flex justify-center w-full pb-4 mt-4 space-x-4 sm:absolute sm:px-4 sm:mt-0"
                >
                    <button
                        :disabled="form.processing || isFileUploading"
                        type="submit"
                        class="text-white w-full justify-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 disabled:bg-blue-200 dark:disabled:bg-blue-900"
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

    <!-- Delete Document -->
    <Transition
        enter-from-class="translate-x-full"
        enter-active-class="transition-transform translate-x-0"
        leave-active-class="transition-transform translate-x-0"
        leave-to-class="translate-x-full"
    >
        <div
            v-if="deleteModalVisibility"
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
                Delete
            </h5>
            <button
                @click="hideDeleteModal"
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
                Are you sure you want to delete this {{ title }}?
            </h3>

            <form @submit.prevent="destroy" class="inline-block">
                <button
                    type="submit"
                    class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm inline-flex items-center px-3 py-2.5 text-center mr-2 dark:focus:ring-red-900"
                >
                    Yes, I'm sure
                </button>
            </form>
            <button
                @click="hideDeleteModal"
                class="text-gray-900 bg-white hover:bg-gray-100 focus:ring-4 focus:ring-blue-300 border border-gray-200 font-medium inline-flex items-center rounded-lg text-sm px-3 py-2.5 text-center dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-gray-700"
                data-modal-toggle="delete-product-modal"
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

                    <div class="-mt-3">
                        <label
                            for="documentUpload"
                            class="text-gray-700 dark:text-neutral-200"
                            >File</label
                        >
                        <FilePond
                            name="documentUpload"
                            ref="pond"
                            label-idle="Upload file here or <span class='filepond--label-action'>Browse</span>"
                            :allow-drop="true"
                            accepted-file-types="application/pdf, application/docx"
                            :file="files"
                            v-model="form.path"
                            :server="server"
                            @init="handleFilePondInit"
                            credits="false"
                        />
                        <p
                            class="text-red-500 text-xs absolute -mt-2"
                            v-if="form.errors.filename"
                        >
                            {{ form.errors.filename }}
                        </p>
                    </div>
                </div>
                <div
                    class="bottom-0 left-0 flex justify-center w-full pb-4 space-x-4 md:px-4 md:absolute"
                >
                    <button
                        type="submit"
                        :disabled="form.processing || isFileUploading"
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
