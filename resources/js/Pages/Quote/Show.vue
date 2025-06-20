<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    quote: Object,
    products: Array, // <-- Add products from controller
});

// Calculations per product
const productMargins = computed(() =>
    props.products.map(product => {
        const cost = Number(product.trade_price);
        const sell = Number(product.retail_price);
        const margin = sell ? ((sell - cost) / sell) * 100 : 0;
        return { ...product, cost, sell, margin };
    })
);
</script>

<template>
    <Head title="Quote Summary" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Quote Summary</h2>
        </template>

        <div class="py-12">
            <div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">
                <div class="mb-6">
                    <p><strong>Customer:</strong> {{ quote.customer_name }}</p>
                    <p><strong>Address:</strong> {{ quote.customer_address }}</p>
                    <p><strong>Date:</strong> {{ new Date(quote.created_at).toLocaleDateString() }}</p>
                </div>

                <table class="w-full text-left border mb-6">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2">Name</th>
                            <th class="px-4 py-2">SKU</th>
                            <th class="px-4 py-2">Cost</th>
                            <th class="px-4 py-2">Sell</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="product in productMargins"
                            :key="product.id"
                            :class="{
                                'bg-red-100': product.margin < 10,
                                'bg-yellow-100': product.margin >= 10 && product.margin < 20
                            }"
                            class="border-b"
                        >
                            <td class="px-4 py-2">{{ product.name }}</td>
                            <td class="px-4 py-2">{{ product.sku }}</td>
                            <td class="px-4 py-2">£{{ product.cost.toFixed(2) }}</td>
                            <td class="px-4 py-2">£{{ product.sell.toFixed(2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="mb-6 space-y-1">
                    <p><strong>Estimated Labor Hours:</strong> {{ quote.labor_hours }}</p>
                    <p><strong>Labor Cost Per Hour:</strong> £{{ quote.labor_cost_per_hour }}</p>
                    <p><strong>Fixed Overheads:</strong> £{{ quote.fixed_overheads }}</p>
                    <p><strong>Target Profit Margin:</strong> {{ quote.target_profit_margin }}%</p>
                    <p><strong>Calculated Margin:</strong> {{ quote.calculated_margin }}%</p>
                    <p><strong>Total Profit:</strong> £{{ Number(quote.total_profit).toFixed(2) }}</p>
                </div>

                <div class="mb-6">
                    <strong>Health Status:</strong>
                    <span
                        :class="{
                            'text-green-600': quote.health_status === 'green',
                            'text-yellow-600': quote.health_status === 'amber',
                            'text-red-600': quote.health_status === 'red'
                        }"
                    >
                        {{ quote.health_status.toUpperCase() }}
                    </span>
                </div>

                <div v-if="quote.ai_suggestions" class="mb-6">
                    <h3 class="font-semibold mb-2">AI Suggestions</h3>
                    <div class="bg-gray-50 p-4 rounded shadow whitespace-pre-line">
                        {{ quote.ai_suggestions }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
