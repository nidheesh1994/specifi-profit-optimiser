<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    products: Array,
    settings: Object,
});

const showProductModal = ref(false);
const selectedProductIds = ref([]);
const selectedProducts = ref([]);
const selectedTemp = ref([]);
const searchQuery = ref('');

const filteredProducts = computed(() => {
    if (!searchQuery.value) return props.products;

    const query = searchQuery.value.toLowerCase();
    return props.products.filter(product =>
        product.name?.toLowerCase().includes(query) ||
        product.sku?.toLowerCase().includes(query) ||
        product.mpn?.toLowerCase().includes(query)
    );
});


const confirmProductSelection = () => {
    selectedProducts.value = [...selectedTemp.value];
    selectedProductIds.value = selectedTemp.value.map(p => p.id);
    showProductModal.value = false;
};

const openProductModal = () => {
    selectedTemp.value = [...selectedProducts.value]; // preserve previous selection
    showProductModal.value = true;
    searchQuery.value = ''; // reset search query
};

const notification = ref(null);
const showNotification = (message, type = 'error') => {
    notification.value = { message, type };
    setTimeout(() => notification.value = null, 3000);
};

const form = ref({
    customer_name: '',
    customer_address: '',
    labor_hours: '',
    labor_cost_per_hour: '',
    fixed_overheads: '',
    target_profit_margin: '',
});

const loadDefaults = () => {
    form.value.labor_hours = props.settings.labor_hours;
    form.value.labor_cost_per_hour = props.settings.labor_cost_per_hour;
    form.value.fixed_overheads = props.settings.fixed_overheads;
    form.value.target_profit_margin = props.settings.target_profit_margin;
};

const createQuote = () => {
    if (selectedProductIds.value.length === 0) {
        showNotification('Please select at least one product.');
        return;
    }

    if (!form.value.customer_name) {
        showNotification('Customer name is required');
        return;
    }

    if (!form.value.labor_hours || form.value.labor_hours <= 0) {
        showNotification('Estimated Labor Hours must be greater than 0.');
        return;
    }

    if (!form.value.labor_cost_per_hour || form.value.labor_cost_per_hour <= 0) {
        showNotification('Labor Cost Per Hour must be greater than 0.');
        return;
    }

    router.post(route('quotes.store'), {
        products: selectedProductIds.value,
        ...form.value,
    });
};
</script>

<template>

    <Head title="Profit Optimiser" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Create Qoutes</h2>
        </template>

        <div class="py-12">
            <div v-if="notification"
                :class="`fixed top-20 right-20 px-4 py-2 rounded shadow text-white z-50 ${notification.type === 'error' ? 'bg-red-600' : 'bg-green-600'}`">
                {{ notification.message }}
            </div>
            <div class="max-w-5xl mx-auto bg-white p-6 rounded shadow">

                <!-- Product Selection -->
                <button @click="openProductModal"
                    class="mb-4 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                    Select Products
                </button>

                <!-- Selected Products Table -->
                <div v-if="selectedProducts.length" class="mb-6">
                    <table class="w-full text-left border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2">Name</th>
                                <th class="px-4 py-2">SKU</th>
                                <th class="px-4 py-2">MPN</th>
                                <th class="px-4 py-2">Cost</th>
                                <th class="px-4 py-2">Sell</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="product in selectedProducts" :key="product.id" class="border-b">
                                <td class="px-4 py-2">{{ product.name }}</td>
                                <td class="px-4 py-2">{{ product.sku }}</td>
                                <td class="px-4 py-2">{{ product.mpn }}</td>
                                <td class="px-4 py-2">£{{ product.trade_price }}</td>
                                <td class="px-4 py-2">£{{ product.retail_price }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Quote Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block font-medium mb-1">Customer Name</label>
                        <input type="text" v-model="form.customer_name" class="w-full border px-3 py-2 rounded" />
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Customer Address</label>
                        <input type="text" v-model="form.customer_address" class="w-full border px-3 py-2 rounded" />
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Estimated Labor Hours</label>
                        <input type="number" v-model="form.labor_hours" class="w-full border px-3 py-2 rounded" />
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Labor Cost Per Hour (£)</label>
                        <input type="number" v-model="form.labor_cost_per_hour"
                            class="w-full border px-3 py-2 rounded" />
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Fixed Overheads (£)</label>
                        <input type="number" v-model="form.fixed_overheads" class="w-full border px-3 py-2 rounded" />
                    </div>
                    <div>
                        <label class="block font-medium mb-1">Target Profit Margin (%)</label>
                        <input type="number" v-model="form.target_profit_margin"
                            class="w-full border px-3 py-2 rounded" />
                    </div>
                </div>

                <button @click="loadDefaults" class="mb-4 bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">
                    Load Default
                </button>

                <button @click="createQuote" class="ml-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    Create Quote
                </button>
            </div>
        </div>

        <!-- Product Modal -->
        <div v-if="showProductModal" class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded shadow-lg max-w-6xl w-full">
                <h3 class="text-lg font-semibold mb-4">Select Products</h3>
                <input type="text" v-model="searchQuery" placeholder="Search products..."
                    class="w-full border px-3 py-2 rounded mb-4" />

                <div class="max-h-80 overflow-y-auto border rounded">
                    <table class="w-full text-left">
                        <thead class="bg-gray-100 sticky top-0">
                            <tr>
                                <th class="px-4 py-2">Select</th>
                                <th class="px-4 py-2">Name</th>
                                <th class="px-4 py-2">SKU</th>
                                <th class="px-4 py-2">MPN</th>
                                <th class="px-4 py-2">Cost</th>
                                <th class="px-4 py-2">Sell</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="product in filteredProducts" :key="product.id" class="border-b">
                                <td class="px-4 py-2">
                                    <input type="checkbox" :value="product" v-model="selectedTemp" />
                                </td>
                                <td class="px-4 py-2">{{ product.name }}</td>
                                <td class="px-4 py-2">{{ product.sku }}</td>
                                <td class="px-4 py-2">{{ product.mpn }}</td>
                                <td class="px-4 py-2">£{{ product.trade_price }}</td>
                                <td class="px-4 py-2">£{{ product.retail_price }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end space-x-2">
                    <button @click="showProductModal = false" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                        Cancel
                    </button>
                    <button @click="confirmProductSelection"
                        class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        Done
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
