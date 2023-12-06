<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import Checkbox from "@/Components/Checkbox.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import InputField from "@/Components/InputField.vue";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        remember: form.remember ? "on" : "",
    })).post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Log in" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
            Sign in to platform
        </h2>

        <form class="mt-8 space-y-8" @submit.prevent="submit">
            <div class="mt-4">
                <InputField
                    id="email"
                    v-model="form.email"
                    type="text"
                    label="Email Address"
                    placeholder="Email Address"
                    :error="form.errors.email"
                />
            </div>

            <div class="mt-6">
                <InputField
                    id="password"
                    v-model="form.password"
                    type="password"
                    label="Password"
                    placeholder="Password"
                    :error="form.errors.password"
                />
            </div>

            <div class="mt-6 flex justify-between">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span
                        class="ml-2 text-sm md:text-md font-medium text-gray-900 dark:text-white"
                        >Remember me</span
                    >
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm md:text-md text-blue-500 hover:text-blue-700 transition-all duration-200 font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Forgot password?
                </Link>
            </div>

            <div class="flex mt-6">
                <PrimaryButton
                    class="w-full"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Sign in
                </PrimaryButton>
            </div>

            <!-- <div class="mb-4 mt-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Don’t have an account yet?
                    <Link
                        :href="route('register')"
                        class="font-medium text-blue-500 hover:text-blue-700 transition-all duration-200"
                        >Sign up</Link
                    >
                </p>
            </div> -->
        </form>
    </AuthenticationCard>
</template>
