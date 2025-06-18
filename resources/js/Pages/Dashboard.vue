<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-6">

                <!-- Profit Optimiser -->
                <div class="bg-white shadow rounded-lg p-6 hover:shadow-md transition">
                    <h3 class="text-lg font-semibold mb-2">Profit Optimiser</h3>
                    <p class="text-sm text-gray-600 mb-4">Analyse quotes and get AI suggestions.</p>
                    <Link :href="'#'" class="text-indigo-600 hover:underline text-sm">Go to tool →</Link>
                </div>

                <!-- Quote History -->
                <div class="bg-white shadow rounded-lg p-6 hover:shadow-md transition">
                    <h3 class="text-lg font-semibold mb-2">Quote History</h3>
                    <p class="text-sm text-gray-600 mb-4">View and manage previously saved quotes.</p>
                    <Link :href="'#'" class="text-indigo-600 hover:underline text-sm">View history →
                    </Link>
                </div>


                <!-- Product List -->
                <div class="bg-white shadow rounded-lg p-6 hover:shadow-md transition">
                    <h3 class="text-lg font-semibold mb-2">Product List</h3>
                    <p class="text-sm text-gray-600 mb-4">Manage product prices, costs, and availability.</p>
                    <Link :href="'#'" class="text-indigo-600 hover:underline text-sm">View products →</Link>
                </div>

                <!-- Settings -->
                <div class="bg-white shadow rounded-lg p-6 hover:shadow-md transition">
                    <h3 class="text-lg font-semibold mb-2">Settings</h3>
                    <p class="text-sm text-gray-600 mb-4">Configure margins, labor cost, and defaults.</p>
                    <Link :href="'#'" class="text-indigo-600 hover:underline text-sm">Update settings →</Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
