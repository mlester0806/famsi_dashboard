<script setup>
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'
import { router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import SelectInput from "../Components/SelectInput.vue";
import { ref, watch, reactive, computed } from "vue";
import debounce from "lodash.debounce";

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const props = defineProps({
    filters: Object,
    applications: Object,
    overallReports: Object,
    monthlyReports: Object,
    dailyReports: Object,
});

const page = usePage();

let reportType = ref(props.filters.type || 'Overall');
let selectMonth = ref(props.filters.month);
let selectYear = ref(props.filters.year);
let chartReports = ref(props.overallReports);
let dailyReports = ref(props.dailyReports);
let monthlyReports = ref(props.monthlyReports);
let overallReports = ref(props.overallReports);

let reportData = ref(props.filters.applications);

const isOverallReport = computed(() => reportType.value === 'Overall' || reportType.value === 'Monthly');

const isMonthlyReport = computed(() => reportType.value === 'Overall');

const chartDataLabels = ref(['Overall']);

const chartData = computed(() => {
    return {
        labels: chartDataLabels.value,
        datasets: [{ label: 'Recruitment', backgroundColor: '#00A36C', data: chartReports.value }]
    }
});

const chartOptions = reactive({
    responsive: true
})

const getDaysInMonth = (year, month) => {
    // Map month names to their respective indices
    const monthIndices = {
        January: 0, February: 1, March: 2, April: 3, May: 4, June: 5,
        July: 6, August: 7, September: 8, October: 9, November: 10, December: 11
    };

    const monthIndex = monthIndices[month];
    if (monthIndex === undefined) {
        // Handle invalid month names
        return 0;
    }

    return new Date(year, monthIndex + 1, 0).getDate();
};

watch(
    reportType,
    debounce((value) => {
        const query = {};
        if (value) {
            if (value === 'Overall') {
                query.month = undefined;
                query.year = undefined;
                selectMonth.value = undefined;
                selectYear.value = undefined;
                chartDataLabels.value = ['Overall'];
                chartReports.value = overallReports.value;
            }

            if (value === 'Monthly') {
                query.month = undefined;
                selectMonth.value = undefined;

                if (!selectYear.value) {
                    const newYear = 2023;
                    selectYear.value = newYear;
                    query.year = newYear;
                } else {
                    query.year = selectYear.value;
                }

                chartDataLabels.value = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                chartReports.value = monthlyReports.value;
            }

            if (value === 'Daily') {
                if (!selectYear.value) {
                    const newYear = 2023;
                    selectYear.value = newYear;
                    query.year = newYear;
                } else {
                    query.year = selectYear.value;
                }

                if (!selectMonth.value) {
                    const currentMonth = 'January';
                    selectMonth.value = currentMonth;
                    query.month = currentMonth;
                } else {
                    query.month = selectMonth.value;
                }

                const daysInMonth = getDaysInMonth(selectYear.value, selectMonth.value);
                chartDataLabels.value = Array.from({ length: daysInMonth }, (_, index) => index + 1);
                chartReports.value = dailyReports.value;
            }

            query.type = value;
        }

        if (selectMonth.value) {
            query.month = selectMonth.value;
        }

        if (selectYear.value) {
            query.year = selectYear.value;
        }

        router.get(`/${page.props.user.role}/reports`, query, {
            preserveState: true,
            replace: true,
        });
    }, 500)
);

watch(
    selectMonth,
    debounce((value) => {
        const query = {};
        if (value) {
            const daysInMonth = getDaysInMonth(selectYear.value, value);
            chartDataLabels.value = Array.from({ length: daysInMonth }, (_, index) => index + 1);
            chartReports.value = dailyReports.value;
            query.month = value;
        }

        if (reportType.value) {
            query.type = reportType.value;
        }

        if (selectYear.value) {
            query.year = selectYear.value;
        }

        console.log(dailyReports.value);

        router.get(`/${page.props.user.role}/reports`, query, {
            preserveState: true,
            replace: true,
        });
    }, 500)
);

watch(
    selectYear,
    debounce((value) => {
        const query = {};
        if (value) {
            if (reportType.value === 'Daily') {
                const daysInMonth = getDaysInMonth(value, selectMonth.value);
                chartDataLabels.value = Array.from({ length: daysInMonth }, (_, index) => index + 1);
                chartReports.value = dailyReports.value;
            } else {
                chartReports.value = monthlyReports.value;
            }
            query.year = value;
        }

        if (selectMonth.value) {
            query.month = selectMonth.value;
        }

        if (reportType.value) {
            query.type = reportType.value;
        }

        router.get(`/${page.props.user.role}/reports`, query, {
            preserveState: true,
            replace: true,
        });
    }, 500)
);


watch(() => props.monthlyReports, (newVal) => {
    if (reportType.value === 'Monthly') {
        chartReports.value = newVal;
        monthlyReports.value = newVal;
    }
});

watch(() => props.dailyReports, (newVal) => {
    if (reportType.value === 'Daily') {
        chartReports.value = newVal;
        dailyReports.value = newVal;
    }
});
</script>

<template>
    <AppLayout title="Dashboard">
        <div class="px-4 pt-6">
            <div class="mb-8">
                <nav class="flex mb-5" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 text-sm font-medium md:space-x-2">
                        <li class="inline-flex items-center">
                            <p class="inline-flex items-center text-gray-700 dark:text-gray-300">
                                <font-awesome-icon class="w-5 h-5 mr-2.5" :icon="['fas', 'house']" />
                                Menu
                            </p>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-400 md:ml-2 dark:text-gray-500"
                                    aria-current="page">Reports</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">
                    Reports
                </h1>
                <span class="text-base font-normal text-gray-500 dark:text-gray-400">This is the reports about recruitment
                    process</span>
            </div>
            <div
                class="items-center justify-center space-x-12 block sm:flex md:divide-x md:divide-gray-100 dark:divide-gray-700">
                <div class="flex mb-8">
                    <div class="relative w-48 sm:w-64">
                        <SelectInput id="reportType" v-model="reportType" label="Type of Report" :canSearch="false">
                            <option value="Overall" :selected="reportType === 'Overall'">
                                Overall
                            </option>
                            <option value="Monthly" :selected="reportType === 'Monthly'">
                                Monthly
                            </option>
                            <option value="Daily" :selected="reportType === 'Daily'">
                                Daily
                            </option>

                        </SelectInput>
                    </div>
                </div>
                <div class="flex mb-8">
                    <div class="relative w-48 sm:w-64">
                        <SelectInput :key="isOverallReport" id="selectMonth" v-model="selectMonth" label="Select Month"
                            :canSearch="false" :disabled="isOverallReport">
                            <option value="January" :selected="selectMonth === 'January'">
                                January
                            </option>

                            <option value="February" :selected="selectMonth === 'February'">
                                February
                            </option>

                            <option value="March" :selected="selectMonth === 'March'">
                                March
                            </option>

                            <option value="April" :selected="selectMonth === 'April'">
                                April
                            </option>

                            <option value="May" :selected="selectMonth === 'May'">
                                May
                            </option>

                            <option value="June" :selected="selectMonth === 'June'">
                                June
                            </option>

                            <option value="July" :selected="selectMonth === 'July'">
                                July
                            </option>

                            <option value="August" :selected="selectMonth === 'August'">
                                August
                            </option>

                            <option value="September" :selected="selectMonth === 'September'">
                                September
                            </option>

                            <option value="October" :selected="selectMonth === 'October'">
                                October
                            </option>

                            <option value="November" :selected="selectMonth === 'November'">
                                November
                            </option>

                            <option value="December" :selected="selectMonth === 'December'">
                                December
                            </option>

                        </SelectInput>
                    </div>
                </div>
                <div class="flex mb-8">
                    <div class="relative w-48 sm:w-64">
                        <SelectInput :key="isMonthlyReport" id="selectYear" v-model="selectYear" label="Select Year"
                            :canSearch="false" :disabled="isMonthlyReport">
                            <option value="2023" :selected="reportType === '2023'">
                                2023
                            </option>
                            <option value="2024" :selected="reportType === '2024'">
                                2024
                            </option>
                        </SelectInput>
                    </div>
                </div>
            </div>
            <div class="grid w-full grid-cols-1 gap-4 my-4 xl:grid-cols-2 2xl:grid-cols-3">
                <Bar id="my-chart-id" :options="chartOptions" :data="chartData" />
            </div>
            <!-- <p class="my-10 text-sm text-center text-gray-500">
        © 2023
        <a
            href="https://www.facebook.com/FAMSILaguna/"
            class="hover:underline"
            target="_blank"
            >Fully Advanced Manpower Solutions, Inc.</a
        >. All rights reserved.
    </p> -->
        </div>
    </AppLayout>
</template>
