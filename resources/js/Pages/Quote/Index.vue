<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    quotes: Object
});

const deleteQuote = (id) => {
    if (confirm('Are you sure you want to delete this quote?')) {
        router.delete(route('quotes.destroy', id));
    }
};
</script>

<template>

    <Head title="Quotes" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Quotes</h2>
                <Link href="/quotes/create" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                Create New Quote
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-6xl mx-auto bg-white p-6 rounded shadow">
                <table class="w-full text-left border mb-6">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2">Customer</th>
                            <th class="px-4 py-2">Created At</th>
                            <th class="px-4 py-2">Margin</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="quote in quotes.data" :key="quote.id" class="border-b">
                            <td class="px-4 py-2">{{ quote.customer_name }}</td>
                            <td class="px-4 py-2">{{ new Date(quote.created_at).toLocaleDateString() }}</td>
                            <td class="px-4 py-2">{{ quote.calculated_margin }}%</td>
                            <td class="px-4 py-2 capitalize">
                                <div class="w-6 h-6 rounded ml-4" :class="{
                                    'bg-green-500': quote.health_status === 'green',
                                    'bg-yellow-400': quote.health_status === 'amber',
                                    'bg-red-500': quote.health_status === 'red'
                                }" title="Health Status"></div>
                            </td>
                            <td class="px-4 py-2">
                                <Link :href="route('quotes.show', quote.id)" class="text-indigo-600 hover:underline">
                                View
                                </Link>&nbsp;|&nbsp;
                                <button @click="deleteQuote(quote.id)" class="text-red-600 hover:underline">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="flex justify-between items-center">
                    <div>
                        Showing {{ quotes.from }} to {{ quotes.to }} of {{ quotes.total }} results
                    </div>
                    <div class="space-x-2">
                        <button :disabled="!quotes.prev_page_url" @click="router.visit(quotes.prev_page_url)"
                            class="px-3 py-1 border rounded hover:bg-gray-100">
                            Prev
                        </button>
                        <button :disabled="!quotes.next_page_url" @click="router.visit(quotes.next_page_url)"
                            class="px-3 py-1 border rounded hover:bg-gray-100">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
