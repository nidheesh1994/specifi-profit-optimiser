<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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


const loadingSuggestion = ref(false);
const showSuggestion = ref(true);
const aiContext = ref('');

const generateAISuggestion = () => {
    loadingSuggestion.value = true;
    router.post(
        route('quotes.generateSuggestion', props.quote.id),
        { context: aiContext.value },
        {
            preserveScroll: true,
            onFinish: () => loadingSuggestion.value = false
        }
    );
};

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
                            <th class="px-4 py-2">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="product in productMargins" :key="product.id" :class="{
                            'bg-red-100': product.margin < 10,
                            'bg-yellow-100': product.margin >= 10 && product.margin < 20
                        }" class="border-b">
                            <td class="px-4 py-2">{{ product.name }}</td>
                            <td class="px-4 py-2">{{ product.sku }}</td>
                            <td class="px-4 py-2">£{{ product.cost.toFixed(2) }}</td>
                            <td class="px-4 py-2">£{{ product.sell.toFixed(2) }}</td>
                            <td class="px-4 py-2">{{ product.margin.toFixed(2) }}%</td>
                        </tr>
                    </tbody>
                </table>

                <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 gap-x-12 gap-y-2">
                    <p><strong>Estimated Labor Hours:</strong> {{ quote.labor_hours }}</p>
                    <p><strong>Labor Cost Per Hour:</strong> £{{ quote.labor_cost_per_hour }}</p>
                    <p><strong>Fixed Overheads:</strong> £{{ quote.fixed_overheads }}</p>
                    <p><strong>Target Profit Margin:</strong> {{ quote.target_profit_margin }}%</p>
                    <p><strong>Calculated Margin:</strong> {{ quote.calculated_margin }}%</p>
                    <p><strong>Total Profit:</strong> £{{ Number(quote.total_profit).toFixed(2) }}</p>
                </div>


                <div class="mb-6 flex">
                    <strong>Health Status:</strong>
                    <div class="w-6 h-6 rounded ml-4" :class="{
                        'bg-green-500': quote.health_status === 'green',
                        'bg-yellow-400': quote.health_status === 'amber',
                        'bg-red-500': quote.health_status === 'red'
                    }" title="Health Status"></div>
                </div>

                <div class="mb-6 flex items-center space-x-4">
                    <input v-model="aiContext" type="text" class="border px-3 py-2 rounded w-full md:w-2/3"
                        placeholder="Optional context for AI suggestion..." />
                    <button @click="generateAISuggestion"
                        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
                        :disabled="loadingSuggestion">
                        {{ loadingSuggestion ? 'Generating...' : 'Generate AI Suggestion' }}
                    </button>
                </div>

                <div v-if="quote.ai_suggestions" class="mb-6">
                    <button @click="showSuggestion = !showSuggestion" class="text-sm text-blue-600 underline mb-2">
                        {{ showSuggestion ? 'Hide' : 'Show' }} AI Suggestion
                    </button>

                    <div v-if="showSuggestion" class="bg-gray-50 p-4 rounded shadow whitespace-pre-line">
                        <h3 class="font-semibold mb-2">AI Suggestions</h3>
                        {{ quote.ai_suggestions }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
