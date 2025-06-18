<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Specifi Profit Optimiser" />

    <div class="min-h-screen bg-gray-100 flex flex-col items-center justify-center text-gray-800">
        <div class="w-full max-w-2xl text-center">
            <img src="/images/logo.png" alt="Logo" class="mx-auto mb-8 w-32 h-auto" />

            <h1 class="text-3xl font-bold mb-4">Specifi Profit Optimiser</h1>
            <p class="text-lg mb-6">Quickly assess quote profitability and get smart AI-driven suggestions.</p>

            <div class="space-x-4">
                <Link
                    v-if="canLogin"
                    :href="route('login')"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
                >
                    Log In
                </Link>
            </div>
        </div>
    </div>
</template>
