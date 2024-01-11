<script setup>
import { router, usePage, useForm, Link } from "@inertiajs/vue3";
import { ref, watch, computed, reactive, Transition, Teleport } from "vue";
import debounce from "lodash.debounce";
import { useToast } from "vue-toastification";
import Pagination from "../Partials/Table/Pagination.vue";
import InputField from "./InputField.vue";
import SelectInput from "./SelectInput.vue";
import FileInput from "./FileInput.vue";

const props = defineProps({
    roles: Object,
    pagination: Object,
    filters: Object,
    linkName: String,
    title: String,
    jobPositions: Array,
    locations: Array,
});

let selectedLocation = ref(props.filters.location);
let totalInputFiles = ref(1);

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
    additional_files: null,
    famsi_files: null,
    file_name_1: "",
    file_path_1: "",
    file_name_2: "",
    file_path_2: "",
    file_name_3: "",
    file_path_3: "",
    file_name_4: "",
    file_path_4: "",
    file_name_5: "",
    file_path_5: "",
    file_name_6: "",
    file_path_6: "",
    file_name_7: "",
    file_path_7: "",
    file_name_8: "",
    file_path_8: "",
    file_name_9: "",
    file_path_9: "",
    file_name_10: "",
    file_path_10: "",
    file_name_11: "",
    file_path_11: "",
    file_name_12: "",
    file_path_12: "",
    file_name_13: "",
    file_path_13: "",
    file_name_14: "",
    file_path_14: "",
    file_name_15: "",
    file_path_15: "",
});

const alreadySubmitted = ref(Boolean(form.famsi_files));

const formFile = reactive([{
    file_name: null,
    file_path: null,
}]);

const finalFormFile = useForm({
    files: {}
});

const errors = reactive([
    {
        file_name: null,
        file_path: null,
    }
]);

const incrementCount = () => {
    if (formFile.length !== 15) {
        formFile.push({
            file_name: null,
            file_path: null,
        });
    }

    if (errors.length !== 15) {
        errors.push({
            file_name: null,
            file_path: null,
        });
    }
};

const decrementCount = (index) => {
    if (formFile.length > 1) {
        formFile.splice(index, 1);
    }

    if (errors.length > 1) {
        errors.splice(index, 1);
    }
};

const page = usePage();

const currentUser = computed(() => {
    return page.props.user.role;
});

const toast = useToast();

let search = ref(props.filters.search);

let updateModalVisibility = ref(false);
let currentUpdatingUserID = ref(null);
let viewInfoModalVisibility = ref(false);
let hireModalVisibility = ref(false);
let resignModalVisibility = ref(false);
let disapproveModalVisibility = ref(false);

watch(
    selectedLocation,
    debounce((value) => {
        const query = {};
        if (value) {
            query.location = value;
        }

        if (search.value) {
            query.search = search.value;
        }

        router.get(`/${page.props.user.role}/${props.linkName}`, query, {
            preserveState: true,
            replace: true,
        });
    }, 500)
);

const hireApplicant = () => {
    form.put(
        `/${page.props.user.role}/${props.linkName}/hire/${currentUpdatingUserID.value}`,
        {
            onSuccess: () => {
                toast.success("Application hired successfully!");
                hideHireModal();
                hideDisapproveModal();
                form.reset();
                clearErrors();
            },
        }
    );
};

const resign = () => {
    form.put(
        `/${page.props.user.role}/${props.linkName}/resign/${currentUpdatingUserID.value}`,
        {
            onSuccess: () => {
                toast.success("Application resigned successfully!");
                hideResignModal();
                form.reset();
                clearErrors();
            },
        }
    );
};

const update = () => {
    // Reset errors
    errors.forEach((error) => {
        Object.keys(error).forEach((key) => {
            error[key] = null;
        });
    });

    // Loop through form items and check for null
    formFile.forEach((item, index) => {
        Object.keys(item).forEach((key) => {
            if (item[key] === null) {
                errors[index][key] = `You need to upload ${key}.`;
            }
        });
    });

    // Check if there are any errors before proceeding with the upload
    const hasErrors = errors.some((error) => Object.values(error).some((value) => value !== null));

    if (!hasErrors) {
        const formData = new FormData();

        formFile.forEach((item, index) => {
            Object.keys(item).forEach((key) => {
                formData.append(`${key}_${index + 1}`, item[key]);
            });
        });

        // finalFormFile.files = formData;

        router.post(
            `/${page.props.user.role}/${props.linkName}/update/${currentUpdatingUserID.value}`,
            formData,
            {
                onSuccess: () => {
                    toast.success("Employees documents updated successfully!");
                    hideHireModal();
                    hideUpdateModal();
                    hideInfoModal();
                    form.reset();
                    finalFormFile.reset();
                    clearErrors();
                },
                onError: () => {
                    console.log(finalFormFile.errors);
                }
            }
        );
    }

};

const enableEdit = () => {
    alreadySubmitted.value = false;
};

// Update Modal
const showUpdateModal = (data) => {
    document.body.classList.remove("overflow-hidden");
    viewInfoModalVisibility.value = false;

    let fileCount = 1;
    let additionalFilesObj = {};

    if (data) {
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
        form.additional_files = data.additional_files;
        form.famsi_files = data.famsi_files;

        if (data.famsi_files) {
            const parsedFamsiFiles = JSON.parse(data.famsi_files);

            Object.keys(parsedFamsiFiles).forEach((key) => {
                if (fileCount === 1) {
                    formFile.length = 0;
                }

                if (key.includes('file_name')) {
                    additionalFilesObj.file_name = parsedFamsiFiles[key]
                }

                if (key.includes('file_path')) {
                    additionalFilesObj.file_path = null
                }

                if (fileCount % 2 === 0) {
                    formFile.push(additionalFilesObj);

                    additionalFilesObj = {};
                }

                fileCount++;
            });
        }

        alreadySubmitted.value = Boolean(data.famsi_files);

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

    formFile.length = 0;

    formFile.push({
        file_name: null,
        file_path: null,
    });

    form.reset();

    form.clearErrors();
};

// Resign Modal
const showResignModal = (id) => {
    document.body.classList.remove("overflow-hidden");
    viewInfoModalVisibility.value = false;

    document.body.classList.add("overflow-hidden");

    currentUpdatingUserID.value = id;

    resignModalVisibility.value = true;
};

const hideResignModal = () => {
    document.body.classList.remove("overflow-hidden");

    currentUpdatingUserID.value = null;

    viewInfoModalVisibility.value = false;
    resignModalVisibility.value = false;
};

// Hire Modal
const showHireModal = (id) => {
    document.body.classList.remove("overflow-hidden");
    viewInfoModalVisibility.value = false;

    document.body.classList.add("overflow-hidden");

    currentUpdatingUserID.value = id;

    hireModalVisibility.value = true;
};

const hideHireModal = () => {
    document.body.classList.remove("overflow-hidden");

    currentUpdatingUserID.value = null;

    viewInfoModalVisibility.value = false;
    hireModalVisibility.value = false;
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

    if (data.additional_files) {
        const parsedAdditionalFiles = JSON.parse(data.additional_files);

        Object.keys(parsedAdditionalFiles).forEach((key) => {
            form[key] = parsedAdditionalFiles[key];
        });
    }

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

        if (selectedLocation.value) {
            query.location = selectedLocation.value;
        }

        router.get(`/${page.props.user.role}/${props.linkName}`, query, {
            preserveState: true,
            replace: true,
        });
    }, 500)
);
</script>

<template>
    <Transition enter-active-class="transition ease-in-out duration-300"
        leave-active-class="transition ease-in-out duration-300">
        <Teleport to="body">
            <div v-if="disapproveModalVisibility" @click="hideDisapproveModal"
                class="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-30 transition duration-200"></div>

            <div v-else-if="resignModalVisibility" @click="hideResignModal"
                class="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-30 transition duration-200"></div>

            <div v-else-if="updateModalVisibility" @click="hideUpdateModal"
                class="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-30 transition duration-200"></div>

            <div v-else-if="viewInfoModalVisibility" @click="hideInfoModal"
                class="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-30 transition duration-200"></div>

            <div v-else-if="hireModalVisibility" @click="hideHireModal"
                class="bg-gray-900 bg-opacity-50 dark:bg-opacity-80 fixed inset-0 z-30 transition duration-200"></div>
        </Teleport>
    </Transition>
    <div
        class="p-4 bg-white block sm:flex items-center justify-between border-b border-gray-200 lg:mt-1.5 dark:bg-gray-800 dark:border-gray-700">
        <div class="w-full mb-1">
            <div class="mb-4">
                <nav class="flex mb-5" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 text-sm font-medium md:space-x-2">
                        <li class="inline-flex items-center">
                            <slot name="first-tab"></slot>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-400 md:ml-2 dark:text-gray-500" aria-current="page">
                                    <slot name="second-tab"></slot>
                                </span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">
                    <slot name="title"></slot>
                </h1>
                <span class="text-base font-normal text-gray-500 dark:text-gray-400">
                    <slot name="description"></slot>
                </span>
            </div>
            <div class="items-center justify-between block sm:flex md:divide-x md:divide-gray-100 dark:divide-gray-700">
                <div class="flex items-center mb-4 sm:mb-0">
                    <div class="relative w-48 mt-1 sm:w-64 xl:w-96">
                        <InputField id="search" v-model="search" type="search" label="Search" placeholder="Search" />

                        <div class="flex items-center mb-4 sm:mb-0">
                            <div class="relative sm:w-64 xl:w-96">
                                <SelectInput id="selectedLocation" v-model="selectedLocation" label="Select Location"
                                    :canSearch="false">
                                    <option value="" disabled selected hidden></option>

                                    <option value="All">
                                        All
                                    </option>
                                    <option :selected="selectedLocation === location.title" v-for="location in props.locations.filter(
                                        (loc) => loc.is_active
                                    )" :key="location.id" :value="location.title">
                                        {{ location.title }}
                                    </option>


                                </SelectInput>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="flex flex-col">
        <div class="overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
                <div class="overflow-hidden shadow">
                    <table class="min-w-full divide-y divide-gray-200 table-fixed dark:divide-gray-600">
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    First Name
                                </th>
                                <th scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Middle Name
                                </th>
                                <th scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Last Name
                                </th>
                                <th scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Gender
                                </th>
                                <th scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Email Address
                                </th>
                                <th scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Contact Number
                                </th>

                                <th scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Job ID
                                </th>

                                <th scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Job Title
                                </th>

                                <th scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Location
                                </th>


                                <th scope="col"
                                    class="px-2 py-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            <tr v-for="(role, index) in roles.data" :key="role.id">
                                <td class="p-4 text-sm font-medium text-gray-500 whitespace-nowrap dark:text-gray-400">
                                    <div class="text-base text-gray-900 dark:text-white">
                                        {{ role.first_name }}
                                    </div>
                                </td>
                                <td class="px-2 py-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    <div v-if="role.middle_name" class="text-base text-gray-900 dark:text-white">
                                        {{ role.middle_name }}
                                    </div>

                                    <div v-else class="text-base font-light text-gray-400 dark:text-gray-600">
                                        N/A
                                    </div>
                                </td>
                                <td class="px-2 py-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    <div class="text-base text-gray-900 dark:text-white">
                                        {{ role.last_name }}
                                    </div>
                                </td>
                                <td class="px-2 py-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    <div class="text-base text-gray-900 dark:text-white">
                                        {{ role.gender }}
                                    </div>
                                </td>
                                <td
                                    class="max-w-sm px-2 py-4 overflow-hidden text-base font-normal text-gray-500 truncate xl:max-w-xs dark:text-gray-400">
                                    <div class="text-base text-gray-900 dark:text-white">
                                        {{ role.email }}
                                    </div>
                                </td>

                                <td
                                    class="max-w-sm px-2 py-4 overflow-hidden text-base font-normal text-gray-500 truncate xl:max-w-xs dark:text-gray-400">
                                    <div class="text-base text-gray-900 dark:text-white">
                                        {{ role.contact_number }}
                                    </div>
                                </td>

                                <td
                                    class="max-w-sm px-2 py-4 overflow-hidden text-base font-normal text-gray-500 truncate xl:max-w-xs dark:text-gray-400">
                                    <div class="text-base text-gray-900 dark:text-white">
                                        {{ role.job_id }}
                                    </div>
                                </td>

                                <td
                                    class="max-w-sm px-2 py-4 overflow-hidden text-base font-normal text-gray-500 truncate xl:max-w-xs dark:text-gray-400">
                                    <div class="text-base text-gray-900 dark:text-white">
                                        {{ role.title }}
                                    </div>
                                </td>

                                <td
                                    class="max-w-sm px-2 py-4 overflow-hidden text-base font-normal text-gray-500 truncate xl:max-w-xs dark:text-gray-400">
                                    <div class="text-base text-gray-900 dark:text-white">
                                        {{ role.location }}
                                    </div>
                                </td>



                                <td class="px-2 py-4 space-x-2 whitespace-nowrap">


                                    <button type="button" id="updateProductButton" @click="
                                        showUpdateModal(roles.data[index])
                                        "
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white rounded-lg bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                        Update
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="roles.data.length === 0">
                                <td colspan="11"
                                    class="max-w-sm text-center p-4 overflow-hidden text-base font-normal text-gray-500 truncate xl:max-w-xs dark:text-gray-400">
                                    <div class="text-base text-gray-900 dark:text-white">
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
        class="sticky bottom-0 right-0 items-center w-full p-4 bg-white border-t border-gray-200 sm:flex sm:justify-between dark:bg-gray-800 dark:border-gray-700">
        <Pagination :roles="roles" :pagination="pagination" />
    </div>

    <!-- Options Modal Modal -->
    <Transition enter-from-class="translate-x-full" enter-active-class="transition-transform translate-x-0"
        leave-active-class="transition-transform translate-x-0" leave-to-class="translate-x-full">
        <div v-if="viewInfoModalVisibility" id="drawer-delete-product-default"
            class="fixed top-0 right-0 z-40 w-full h-screen max-w-xs p-4 overflow-y-auto bg-white dark:bg-gray-800"
            tabindex="-1" aria-labelledby="drawer-label" aria-hidden="true">
            <h5 id="drawer-label"
                class="inline-flex items-center text-sm font-semibold text-gray-500 uppercase dark:text-gray-400">
                {{ title }} Information
            </h5>
            <button @click="hideInfoModal" type="button" aria-controls="drawer-delete-product-default"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 absolute top-2.5 right-2.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"></path>
                </svg>
                <span class="sr-only">Close menu</span>
            </button>

            <div class="px-4 py-8 space-y-4">
                <div>
                    <h3 class="text-md text-gray-500 dark:text-white">
                        <span class="font-bold text-black dark:text-gray-400">First Name:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{ form.first_name }}
                    </p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">Middle Name:
                        </span>
                    </h3>
                    <span v-if="form.middle_name">{{ form.middle_name }}</span>
                    <p class="text-gray-500 dark:text-gray-600" v-else>N/A</p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">Last Name:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{ form.last_name }}
                    </p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">Gender:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">{{ form.gender }}</p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">Email Address:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">{{ form.email }}</p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">Contact Number:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{ form.contact_number }}
                    </p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">Job ID:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{ form.job_id }}
                    </p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">Job Title:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{ form.job_title }}
                    </p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">Location:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{ form.location }}
                    </p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">Resume File Name:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{ form.resume_name }}
                    </p>
                </div>

                <div>
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">Resume File Path:
                        </span>
                    </h3>
                    <p class="break-words">
                        <a target="_blank" :href="form.resume_file"
                            class="text-blue-600 hover:text-blue-700 whitespace-normal dark:text-blue-500 dark:hover:text-blue-600">
                            {{ form.resume_file }}
                        </a>
                    </p>
                </div>

                <div v-if="form.notes">
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">Notes:
                        </span>
                    </h3>
                    <p class="text-black dark:text-white">
                        {{ form.notes }}
                    </p>
                </div>

                <div v-if="form.file_name_1 && form.file_path_1">
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">{{ form.file_name_1 }}:
                        </span>
                    </h3>
                    <p class="break-words">
                        <a target="_blank" :href="form.file_path_1"
                            class="text-blue-600 hover:text-blue-700 whitespace-normal dark:text-blue-500 dark:hover:text-blue-600">
                            {{ form.file_path_1 }}
                        </a>
                    </p>
                </div>

                <div v-if="form.file_name_2 && form.file_path_2">
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">{{ form.file_name_2 }}:
                        </span>
                    </h3>
                    <p class="break-words">
                        <a target="_blank" :href="form.file_path_2"
                            class="text-blue-600 hover:text-blue-700 whitespace-normal dark:text-blue-500 dark:hover:text-blue-600">
                            {{ form.file_path_2 }}
                        </a>
                    </p>
                </div>

                <div v-if="form.file_name_3 && form.file_path_3">
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">{{ form.file_name_3 }}:
                        </span>
                    </h3>
                    <p class="break-words">
                        <a target="_blank" :href="form.file_path_3"
                            class="text-blue-600 hover:text-blue-700 whitespace-normal dark:text-blue-500 dark:hover:text-blue-600">
                            {{ form.file_path_3 }}
                        </a>
                    </p>
                </div>

                <div v-if="form.file_name_4 && form.file_path_4">
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">{{ form.file_name_4 }}:
                        </span>
                    </h3>
                    <p class="break-words">
                        <a target="_blank" :href="form.file_path_4"
                            class="text-blue-600 hover:text-blue-700 whitespace-normal dark:text-blue-500 dark:hover:text-blue-600">
                            {{ form.file_path_4 }}
                        </a>
                    </p>
                </div>

                <div v-if="form.file_name_5 && form.file_path_5">
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">{{ form.file_name_5 }}:
                        </span>
                    </h3>
                    <p class="break-words">
                        <a target="_blank" :href="form.file_path_5"
                            class="text-blue-600 hover:text-blue-700 whitespace-normal dark:text-blue-500 dark:hover:text-blue-600">
                            {{ form.file_path_5 }}
                        </a>
                    </p>
                </div>

                <div v-if="form.file_name_6 && form.file_path_6">
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">{{ form.file_name_6 }}:
                        </span>
                    </h3>
                    <p class="break-words">
                        <a target="_blank" :href="form.file_path_6"
                            class="text-blue-600 hover:text-blue-700 whitespace-normal dark:text-blue-500 dark:hover:text-blue-600">
                            {{ form.file_path_6 }}
                        </a>
                    </p>
                </div>

                <div v-if="form.file_name_7 && form.file_path_7">
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">{{ form.file_name_7 }}:
                        </span>
                    </h3>
                    <p class="break-words">
                        <a target="_blank" :href="form.file_path_7"
                            class="text-blue-600 hover:text-blue-700 whitespace-normal dark:text-blue-500 dark:hover:text-blue-600">
                            {{ form.file_path_7 }}
                        </a>
                    </p>
                </div>

                <div v-if="form.file_name_8 && form.file_path_8">
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">{{ form.file_name_8 }}:
                        </span>
                    </h3>
                    <p class="break-words">
                        <a target="_blank" :href="form.file_path_8"
                            class="text-blue-600 hover:text-blue-700 whitespace-normal dark:text-blue-500 dark:hover:text-blue-600">
                            {{ form.file_path_8 }}
                        </a>
                    </p>
                </div>

                <div v-if="form.file_name_9 && form.file_path_9">
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">{{ form.file_name_9 }}:
                        </span>
                    </h3>
                    <p class="break-words">
                        <a target="_blank" :href="form.file_path_9"
                            class="text-blue-600 hover:text-blue-700 whitespace-normal dark:text-blue-500 dark:hover:text-blue-600">
                            {{ form.file_path_9 }}
                        </a>
                    </p>
                </div>

                <div v-if="form.file_name_10 && form.file_path_10">
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">{{ form.file_name_10 }}:
                        </span>
                    </h3>
                    <p class="break-words">
                        <a target="_blank" :href="form.file_path_10"
                            class="text-blue-600 hover:text-blue-700 whitespace-normal dark:text-blue-500 dark:hover:text-blue-600">
                            {{ form.file_path_10 }}
                        </a>
                    </p>
                </div>

                <div v-if="form.file_name_11 && form.file_path_11">
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">{{ form.file_name_11 }}:
                        </span>
                    </h3>
                    <p class="break-words">
                        <a target="_blank" :href="form.file_path_11"
                            class="text-blue-600 hover:text-blue-700 whitespace-normal dark:text-blue-500 dark:hover:text-blue-600">
                            {{ form.file_path_11 }}
                        </a>
                    </p>
                </div>

                <div v-if="form.file_name_12 && form.file_path_12">
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">{{ form.file_name_12 }}:
                        </span>
                    </h3>
                    <p class="break-words">
                        <a target="_blank" :href="form.file_path_12"
                            class="text-blue-600 hover:text-blue-700 whitespace-normal dark:text-blue-500 dark:hover:text-blue-600">
                            {{ form.file_path_12 }}
                        </a>
                    </p>
                </div>

                <div v-if="form.file_name_13 && form.file_path_13">
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">{{ form.file_name_13 }}:
                        </span>
                    </h3>
                    <p class="break-words">
                        <a target="_blank" :href="form.file_path_13"
                            class="text-blue-600 hover:text-blue-700 whitespace-normal dark:text-blue-500 dark:hover:text-blue-600">
                            {{ form.file_path_13 }}
                        </a>
                    </p>
                </div>

                <div v-if="form.file_name_14 && form.file_path_14">
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">{{ form.file_name_14 }}:
                        </span>
                    </h3>
                    <p class="break-words">
                        <a target="_blank" :href="form.file_path_14"
                            class="text-blue-600 hover:text-blue-700 whitespace-normal dark:text-blue-500 dark:hover:text-blue-600">
                            {{ form.file_path_14 }}
                        </a>
                    </p>
                </div>

                <div v-if="form.file_name_15 && form.file_path_15">
                    <h3 class="text-md text-gray-500 dark:text-gray-400">
                        <span class="font-bold text-black dark:text-gray-400">{{ form.file_name_15 }}:
                        </span>
                    </h3>
                    <p class="break-words">
                        <a target="_blank" :href="form.file_path_15"
                            class="text-blue-600 hover:text-blue-700 whitespace-normal dark:text-blue-500 dark:hover:text-blue-600">
                            {{ form.file_path_15 }}
                        </a>
                    </p>
                </div>

                <div class="flex justify-center w-full pt-4 space-x-4">
                    <button @click="showResignModal(currentUpdatingUserID)" type="button" id="deleteJobsButton"
                        class="inline-flex w-full justify-center text-white items-center bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 rounded-lg text-sm font-medium px-5 py-2.5 focus:z-10 dark:bg-red-700 dark:hover:bg-red-900 dark:focus:ring-red-900">
                        Resign
                    </button>

                    <button @click="showUpdateModal(form)"
                        class="text-white w-full justify-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 disabled:bg-blue-200 dark:disabled:bg-blue-900">
                        Update
                    </button>


                </div>
            </div>
        </div>
    </Transition>

    <!-- Resign Product Drawer -->
    <Transition enter-from-class="translate-x-full" enter-active-class="transition-transform translate-x-0"
        leave-active-class="transition-transform translate-x-0" leave-to-class="translate-x-full">
        <div v-if="resignModalVisibility" id="drawer-delete-product-default"
            class="fixed top-0 right-0 z-40 w-full h-screen max-w-xs p-4 overflow-y-auto bg-white dark:bg-gray-800"
            tabindex="-1" aria-labelledby="drawer-label" aria-hidden="true">
            <h5 id="drawer-label"
                class="inline-flex items-center text-sm font-semibold text-gray-500 uppercase dark:text-gray-400">
                Resign
            </h5>
            <button @click="hideResignModal" type="button" aria-controls="drawer-delete-product-default"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 absolute top-2.5 right-2.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"></path>
                </svg>
                <span class="sr-only">Close menu</span>
            </button>
            <svg class="w-10 h-10 mt-8 mb-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="mb-6 text-lg text-gray-500 dark:text-gray-400">
                Are you sure you want to resign this {{ title }}?
            </h3>

            <form @submit.prevent="resign" class="inline-block">
                <button type="submit"
                    class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm inline-flex items-center px-3 py-2.5 text-center mr-2 dark:focus:ring-red-900">
                    Yes, I'm sure
                </button>
            </form>
            <button @click="hideResignModal"
                class="text-gray-900 bg-white hover:bg-gray-100 focus:ring-4 focus:ring-blue-300 border border-gray-200 font-medium inline-flex items-center rounded-lg text-sm px-3 py-2.5 text-center dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600 dark:hover:text-white dark:hover:bg-gray-700 dark:focus:ring-gray-700"
                data-modal-toggle="delete-product-modal">
                No, cancel
            </button>
        </div>
    </Transition>

    <!-- Update Modal -->
    <Transition enter-from-class="translate-x-full" enter-active-class="transition-transform translate-x-0"
        leave-active-class="transition-transform translate-x-0" leave-to-class="translate-x-full">
        <div v-if="updateModalVisibility" id="drawer-update-product-default"
            class="fixed top-0 right-0 z-40 w-full h-screen max-w-xs p-4 overflow-y-auto bg-white dark:bg-gray-800"
            tabindex="-1" aria-labelledby="drawer-label" aria-hidden="true">
            <h5 id="drawer-label"
                class="inline-flex items-center mb-6 text-sm font-semibold text-gray-500 uppercase dark:text-gray-400">
                Update {{ title }}
            </h5>
            <button type="button" @click="hideUpdateModal" aria-controls="drawer-update-product-default"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 absolute top-2.5 right-2.5 inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                    xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"></path>
                </svg>
                <span class="sr-only">Close</span>
            </button>
            <form @submit.prevent="update">
                <div v-if="!alreadySubmitted" class="space-y-10">
                    <div v-for="(file, index) in formFile">
                        <InputField :id="'file_name_' + index" v-model="formFile[index].file_name" type="text"
                            label="File Name *" placeholder="File Name" />
                        <p v-if="errors[index]?.file_name" class="text-red-500 text-sm -mt-3 absolute">{{
                            errors[index].file_name }}</p>

                        <FileInput :id="'file_path_' + index" v-model="formFile[index].file_path"
                            placeholder="Upload File" />
                        <p v-if="errors[index]?.file_path" class="text-red-500 text-sm -mt-8 absolute">{{
                            errors[index].file_path }}</p>
                    </div>
                </div>
                <div v-if="!alreadySubmitted" class="flex justify-center w-full py-4 space-x-4 mt-8">
                    <div class="flex flex-col w-full space-y-4">
                        <button type="button" @click="incrementCount"
                            class="w-full justify-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            Add New Files
                        </button>
                        <button type="submit"
                            class="w-full justify-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            Update
                        </button>
                        <button @click="hideUpdateModal" type="button" aria-controls="drawer-create-product-default"
                            class="inline-flex w-full justify-center text-gray-500 items-center bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                            <svg aria-hidden="true" class="w-5 h-5 -ml-1 sm:mr-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12">
                                </path>
                            </svg>
                            Cancel
                        </button>
                    </div>
                </div>

                <div v-else class="flex justify-center w-full py-4 space-x-4 mt-8">
                    <div class="flex flex-col w-full space-y-4">
                        <button type="button" @click="enableEdit"
                            class="w-full justify-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            Edit Again
                        </button>
                        <button @click="hideUpdateModal" type="button" aria-controls="drawer-create-product-default"
                            class="inline-flex w-full justify-center text-gray-500 items-center bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                            <svg aria-hidden="true" class="w-5 h-5 -ml-1 sm:mr-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12">
                                </path>
                            </svg>
                            Cancel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </Transition>
</template>
