<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Login - ESDM KFPB" />
    
    <div class="min-h-screen bg-gradient-to-br from-blue-20 to-white flex flex-col justify-center">
        <!-- Background Image -->
        <div class="absolute inset-0 z-0">
            <img src="/images/BG_KF1.jpg" alt="Background" class="w-full h-full object-cover" />
            <div class="absolute inset-0 bg-gradient-to-b from-white/85 to-white/80"></div>
        </div>

        <div class="sm:mx-auto sm:w-full sm:max-w-md z-10">
            <div class="flex justify-center mb-6">
                <img src="/images/KF_Logo.png" alt="Kimia Farma Logo" class="h-16 w-auto" />
            </div>
            <h2 class="text-center text-3xl font-bold tracking-tight text-gray-900">
                Selamat Datang Kembali
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Silakan masuk ke akun Anda
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md z-10">
            <div class="bg-white/70 backdrop-blur-md py-8 px-4 shadow-xl rounded-lg border border-gray-200 sm:px-10">
                <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="email" value="Email" class="text-gray-900" />
                        <div class="mt-1">
                            <TextInput
                                id="email"
                                type="email"
                                class="block w-full rounded-md border-gray-300 bg-white focus:border-blue-500 focus:ring-blue-500"
                                v-model="form.email"
                                required
                                autofocus
                                autocomplete="username"
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <div class="flex items-center justify-between">
                            <InputLabel for="password" value="Password" class="text-gray-900" />
                        </div>
                        <div class="mt-1">
                            <TextInput
                                id="password"
                                type="password"
                                class="block w-full rounded-md border-gray-300 bg-white focus:border-blue-500 focus:ring-blue-500"
                                v-model="form.password"
                                required
                                autocomplete="current-password"
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <Checkbox 
                                name="remember" 
                                v-model:checked="form.remember" 
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 checked:bg-blue-600 checked:border-transparent" 
                            />
                            <label for="remember" class="ml-2 block text-sm text-gray-600">Ingat Saya</label>
                        </div>

                        <div class="text-sm">
                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="font-medium text-blue-600 hover:text-blue-500"
                            >
                                Lupa Password?
                            </Link>
                        </div>
                    </div>

                    <div>
                        <PrimaryButton
                            class="flex w-full justify-center rounded-md bg-blue-600 px-3 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Masuk
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center z-10">
            <p class="text-sm text-gray-600">
                &copy; {{ new Date().getFullYear() }} Sistem eSDM Kimia Farma Plant Banjaran 2.0
            </p>
        </div>
    </div>
</template>
