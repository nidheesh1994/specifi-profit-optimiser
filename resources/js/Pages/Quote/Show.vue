<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import jsPDF from 'jspdf';
import html2canvas from 'html2canvas';

const props = defineProps({
    quote: Object,
    products: Array,
    allProducts: Array,
});

const showProductModal = ref(false);
const selectedProducts = ref([...props.products]);
const selectedTemp = ref([]);
const searchQuery = ref('');

const filteredProducts = computed(() => {
    if (!searchQuery.value) return props.allProducts;

    const query = searchQuery.value.toLowerCase();
    return props.allProducts.filter(product =>
        product.name?.toLowerCase().includes(query) ||
        product.sku?.toLowerCase().includes(query) ||
        product.mpn?.toLowerCase().includes(query)
    );
});

const updateQuoteProducts = () => {
    selectedProducts.value = [...selectedTemp.value];
    const selectedProductIds = selectedTemp.value.map(p => p.id);
    showProductModal.value = false;
    router.post(route('quotes.updateProducts', props.quote.id), {
        products: selectedProductIds
    });
};

const openProductModal = () => {
    selectedTemp.value = [...selectedProducts.value];
    showProductModal.value = true;
    searchQuery.value = '';
};


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
            onFinish: () => {
                loadingSuggestion.value = false;
                showSuggestion.value = true;
            }
        }
    );
};

const needsNewSuggestion = computed(() => {
    if (!props.quote.last_ai_feedback) return true;

    const lastFeedback = new Date(props.quote.last_ai_feedback).getTime();
    const lastUpdated = new Date(props.quote.updated_at).getTime();
    return lastFeedback < lastUpdated;
});

const showConstraintModal = ref(false);
const constraintsForm = ref({
    labor_hours: props.quote.labor_hours,
    labor_cost_per_hour: props.quote.labor_cost_per_hour,
    fixed_overheads: props.quote.fixed_overheads,
    target_profit_margin: props.quote.target_profit_margin,
});

const openEditConstraintsModal = () => {
    showConstraintModal.value = true;
};

const saveConstraints = () => {
    router.post(route('quotes.updateDetails', props.quote.id), constraintsForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            showConstraintModal.value = false;
        },
    });
};

const showCustomerModal = ref(false);
const customerForm = ref({
    customer_name: props.quote.customer_name,
    customer_address: props.quote.customer_address,
});

const openCustomerModal = () => {
    showCustomerModal.value = true;
};

const saveCustomerDetails = () => {
    router.post(route('quotes.updateCustomer', props.quote.id), customerForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            showCustomerModal.value = false;
        },
    });
};


const showExportModal = ref(false);
const exportContentRef = ref(null);


const exportPDF = async () => {
    const element = exportContentRef.value;
    const canvas = await html2canvas(element, { scale: 2, height: element.scrollHeight });
    const imgData = canvas.toDataURL('image/png');
    const pdf = new jsPDF({ unit: 'pt', format: 'a4' });

    const pageWidth = pdf.internal.pageSize.getWidth();
    const imgProps = pdf.getImageProperties(imgData);
    const pdfWidth = pageWidth - 40;
    const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

    pdf.addImage(imgData, 'PNG', 20, 20, pdfWidth, pdfHeight);
    pdf.save(`Quote-${props.quote.id}.pdf`);
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
                    <p><strong>ID:</strong> #{{ quote.id }}</p>
                    <p><strong>Customer:</strong> {{ quote.customer_name }}</p>
                    <p><strong>Address:</strong> {{ quote.customer_address }}</p>
                    <p><strong>Date:</strong> {{ new Date(quote.created_at).toLocaleDateString() }}</p>
                </div>

                <div class="mb-4 flex space-x-4">
                    <button @click="openProductModal"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                        Edit Products
                    </button>
                    <button @click="openEditConstraintsModal"
                        class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
                        Edit Constraints
                    </button>
                    <button @click="openCustomerModal"
                        class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
                        Edit Customer
                    </button>
                    <button @click="showExportModal = true"
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                        Export Quote
                    </button>
                </div>


                <table class="w-full text-left border mb-6">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2">Name</th>
                            <th class="px-4 py-2">Category</th>
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
                            <td class="px-4 py-2">{{ product.category }}</td>
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

                </div>

                <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 gap-x-12 gap-y-2">
                    <strong class="underline">Calculations</strong><br />
                    <p><strong>Total cost: </strong> £{{ quote.total_trade_price }}</p>
                    <p><strong>Total sell:</strong> £{{ quote.total_retail_price }}</p>
                    <p><strong>Calculated Margin:</strong> {{ quote.calculated_margin }}%</p>
                    <p><strong>Total Profit:</strong> £{{ Number(quote.total_profit).toFixed(2) }}</p>
                </div>


                <div class="mb-6 flex">
                    <strong>Status:</strong>
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
                        {{ loadingSuggestion ? "Generating Suggestion..." : (needsNewSuggestion ?
                            "Generate New AI Suggestion" :
                            "Generate AI Suggestion") }}
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

        <!-- Customer Modal -->
        <div v-if="showCustomerModal"
            class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded shadow-lg max-w-lg w-full">
                <h3 class="text-lg font-semibold mb-4">Edit Customer Details</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Customer Name</label>
                        <input type="text" v-model="customerForm.customer_name"
                            class="w-full border px-3 py-2 rounded" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Customer Address</label>
                        <textarea v-model="customerForm.customer_address" rows="3"
                            class="w-full border px-3 py-2 rounded"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                    <button @click="showCustomerModal = false"
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                    <button @click="saveCustomerDetails"
                        class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
                        Save
                    </button>
                </div>
            </div>
        </div>


        <!-- Product Modal -->
        <div v-if="showProductModal" class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded shadow-lg max-w-6xl w-full">
                <h3 class="text-lg font-semibold mb-4">Select Products</h3>
                <input type="text" v-model="searchQuery" placeholder="Search products..."
                    class="w-full border px-3 py-2 rounded mb-4" />

                <div class="max-h-80 overflow-y-auto border rounded mb-4">
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
                    <button @click="updateQuoteProducts"
                        class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        Save
                    </button>
                </div>
            </div>
        </div>

        <!-- Constraint Modal -->
        <div v-if="showConstraintModal"
            class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded shadow-lg max-w-lg w-full">
                <h3 class="text-lg font-semibold mb-4">Edit Quote Constraints</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Labor Hours</label>
                        <input type="number" v-model="constraintsForm.labor_hours"
                            class="w-full border px-3 py-2 rounded" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Labor Cost Per Hour</label>
                        <input type="number" v-model="constraintsForm.labor_cost_per_hour"
                            class="w-full border px-3 py-2 rounded" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Fixed Overheads</label>
                        <input type="number" v-model="constraintsForm.fixed_overheads"
                            class="w-full border px-3 py-2 rounded" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Target Profit Margin</label>
                        <input type="number" v-model="constraintsForm.target_profit_margin"
                            class="w-full border px-3 py-2 rounded" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                    <button @click="showConstraintModal = false"
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                        Cancel
                    </button>
                    <button @click="saveConstraints"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Save
                    </button>
                </div>
            </div>
        </div>

        <!-- Export Modal -->
        <div v-if="showExportModal"
            class="fixed inset-0 z-50 bg-black bg-opacity-50 flex pt-10 items-center justify-center overflow-y-auto">
            <div class="bg-white p-6 rounded shadow-lg max-w-4xl w-full mt-auto mb-auto">
                <h3 class="text-lg font-semibold mb-4">Export Quote Preview</h3>

                <div ref="exportContentRef" class="bg-white text-black space-y-4 text-sm leading-relaxed pr-3">
                    <div class="mb-4">
                        <h2 class="text-xl font-bold">Quote Summary</h2>
                        <p><strong>ID:</strong> #{{ quote.id }}</p>
                        <p><strong>Customer:</strong> {{ quote.customer_name }}</p>
                        <p><strong>Address:</strong> {{ quote.customer_address }}</p>
                        <p><strong>Date:</strong> {{ new Date(quote.created_at).toLocaleDateString() }}</p>
                    </div>

                    <table class="w-full text-left border mt-2 text-xs">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-2 py-1">Name</th>
                                <th class="px-2 py-1">Category</th>
                                <th class="px-2 py-1">SKU</th>
                                <th class="px-2 py-1">Cost</th>
                                <th class="px-2 py-1">Sell</th>
                                <th class="px-2 py-1">Margin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="product in productMargins" :key="product.id" class="border-b">
                                <td class="px-2 py-1">{{ product.name }}</td>
                                <td class="px-2 py-1">{{ product.category }}</td>
                                <td class="px-2 py-1">{{ product.sku }}</td>
                                <td class="px-2 py-1">£{{ product.cost.toFixed(2) }}</td>
                                <td class="px-2 py-1">£{{ product.sell.toFixed(2) }}</td>
                                <td class="px-2 py-1">{{ product.margin.toFixed(2) }}%</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1 mt-4">
                        <p><strong>Estimated Labor Hours:</strong> {{ quote.labor_hours }}</p>
                        <p><strong>Labor Cost Per Hour:</strong> £{{ quote.labor_cost_per_hour }}</p>
                        <p><strong>Fixed Overheads:</strong> £{{ quote.fixed_overheads }}</p>
                        <p><strong>Target Profit Margin:</strong> {{ quote.target_profit_margin }}%</p>
                    </div>

                    <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1">
                        <strong class="underline">Calculations</strong><br />
                        <p><strong>Total cost: </strong> £{{ quote.total_trade_price }}</p>
                        <p><strong>Total sell:</strong> £{{ quote.total_retail_price }}</p>
                        <p><strong>Calculated Margin:</strong> {{ quote.calculated_margin }}%</p>
                        <p><strong>Total Profit:</strong> £{{ Number(quote.total_profit).toFixed(2) }}</p>
                    </div>

                    <div v-if="quote.ai_suggestions && showSuggestion" class="mt-4">
                        <h4 class="font-semibold mb-1">AI Suggestion</h4>
                        <div class="whitespace-pre-line text-gray-800 text-sm border p-2 rounded bg-gray-50">
                            {{ quote.ai_suggestions }}
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-2">
                    <button @click="showExportModal = false"
                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                    <button @click="exportPDF" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Export
                        to
                        PDF</button>
                </div>
            </div>
        </div>


    </AuthenticatedLayout>
</template>
