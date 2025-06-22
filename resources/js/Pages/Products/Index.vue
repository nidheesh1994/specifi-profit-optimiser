<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    products: Array,
});

// Modal visibility
const showAddModal = ref(false);

// create Form setup
const form = useForm({
    name: '',
    category: '',
    quantity: '',
    trade_price: '',
    retail_price: '',
    mpn: '',
    sku: '',
});


// edit form
const isEditing = ref(false);
const editingProductId = ref(null);

const openAddModal = () => {
    form.reset();
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
    form.name = '';
    form.category = '';
    form.quantity = '';
    form.trade_price = '';
    form.retail_price = '';
    form.mpn = '';
    form.sku = '';
    editingProductId.value = null;
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('products.update', editingProductId.value), {
            onSuccess: () => {
                closeAddModal();
                isEditing.value = false;
                editingProductId.value = null;
            },
        });
    } else {
        form.post(route('products.store'), {
            onSuccess: () => closeAddModal(),
        });
    }
};



const editProduct = (id) => {
    const product = props.products.find(p => p.id === id);
    if (product) {
        form.name = product.name;
        form.category = product.category;
        form.quantity = product.quantity;
        form.trade_price = product.trade_price;
        form.retail_price = product.retail_price;
        form.mpn = product.mpn;
        form.sku = product.sku;
        editingProductId.value = product.id;
        isEditing.value = true;
        showAddModal.value = true;
    }
};

const deleteProduct = (id) => {
    if (confirm('Are you sure you want to delete this product?')) {
        router.delete(route('products.destroy', id));
    }
};

</script>

<template>

    <Head title="Product List" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Product List
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">All Products</h3>
                    <button @click="openAddModal"
                        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        + Add Product
                    </button>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <table class="min-w-full table-auto text-left">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2">#</th>
                                <th class="px-4 py-2">Name</th>
                                <th class="px-4 py-2">Category</th>
                                <th class="px-4 py-2">Quantity</th>
                                <th class="px-4 py-2">Cost</th>
                                <th class="px-4 py-2">Sell</th>
                                <th class="px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(product, index) in products" :key="product.id" class="border-b">
                                <td class="px-4 py-2">{{ index + 1 }}</td>
                                <td class="px-4 py-2">{{ product.name }}</td>
                                <td class="px-4 py-2">{{ product.category }}</td>
                                <td class="px-4 py-2">{{ product.quantity }}</td>
                                <td class="px-4 py-2">£{{ product.trade_price }}</td>
                                <td class="px-4 py-2">£{{ product.retail_price }}</td>
                                <td class="px-4 py-2 space-x-2">
                                    <button @click="editProduct(product.id)" class="text-blue-600 hover:underline">
                                        Edit
                                    </button>
                                    <button @click="deleteProduct(product.id)" class="text-red-600 hover:underline">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="products.length === 0">
                                <td colspan="5" class="text-center text-gray-500 py-4">
                                    No products found.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Add Product Modal -->
                    <div v-if="showAddModal"
                        class="fixed inset-0 z-50 bg-black bg-opacity-50 flex items-center justify-center">
                        <div class="bg-white p-6 rounded shadow-md w-full max-w-md">
                            <h3 class="text-lg font-semibold mb-4">Add New Product</h3>

                            <form @submit.prevent="submitForm">
                                <div class="mb-4">
                                    <label class="block mb-1 font-medium">Name</label>
                                    <input type="text" v-model="form.name" class="w-full border px-3 py-2 rounded"
                                        required />
                                </div>

                                <div class="mb-4">
                                    <label class="block mb-1 font-medium">Category</label>
                                    <input type="text" v-model="form.category" class="w-full border px-3 py-2 rounded"
                                        required />
                                </div>

                                <div class="mb-4">
                                    <label class="block mb-1 font-medium">Quantity</label>
                                    <input type="number" v-model="form.quantity" class="w-full border px-3 py-2 rounded"
                                        step="1" required />
                                </div>

                                <div class="mb-4">
                                    <label class="block mb-1 font-medium">Trade Price</label>
                                    <input type="number" v-model="form.trade_price"
                                        class="w-full border px-3 py-2 rounded" step="0.01" required />
                                </div>

                                <div class="mb-4">
                                    <label class="block mb-1 font-medium">Retail Price</label>
                                    <input type="number" v-model="form.retail_price"
                                        class="w-full border px-3 py-2 rounded" step="0.01" required />
                                </div>

                                <div class="mb-4">
                                    <label class="block mb-1 font-medium">MPN</label>
                                    <input type="text" v-model="form.mpn" class="w-full border px-3 py-2 rounded" />
                                </div>

                                <div class="mb-4">
                                    <label class="block mb-1 font-medium">SKU</label>
                                    <input type="text" v-model="form.sku" class="w-full border px-3 py-2 rounded" />
                                </div>

                                <div class="flex justify-end space-x-2">
                                    <button type="button" @click="closeAddModal"
                                        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                        Save
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
