<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watchEffect } from 'vue';
import axios from 'axios';

const props = defineProps({
    settings: Object,
});

const page = usePage();
const flashMessage = ref(null);

// ✅ Flash works after redirect now
watchEffect(() => {
  if (page.props.flash?.success) {
    flashMessage.value = page.props.flash.success;

    setTimeout(() => {
      flashMessage.value = null;
    }, 4000);
  }
});
const connectionStatus = ref(props.settings?.connection_status || null); // null, 'success', 'error'

const form = useForm({
    labor_hours: props.settings?.labor_hours,
    labor_cost_per_hour: props.settings?.labor_cost_per_hour,
    fixed_overheads: props.settings?.fixed_overheads,
    target_profit_margin: props.settings?.target_profit_margin,
    llm_provider: props.settings?.llm_provider,
    api_key: props.settings?.api_key,
    model_name: props.settings?.model_name,
    connection_status: props.settings?.connection_status,
});

const availableModels = computed(() => {
    if (form.llm_provider === 'openai') {
        return ['gpt-4o', 'gpt-4.1', 'gpt-4', 'gpt-3.5-turbo'];
    }
    return [];
});

const checkConnection = async () => {
    if (!form.llm_provider || !form.api_key || !form.model_name) {
        connectionStatus.value = 'error';
        return;
    }
    try {
        const response = await axios.post(route('settings.test-connection'), {
            provider: form.llm_provider,
            modal_name: form.model_name,
            api_key: form.api_key,
            model_name: form.model_name,
        });

        connectionStatus.value = response.data.status ?? 'error';
        form.connection_status = connectionStatus.value;
    } catch {
        connectionStatus.value = 'error';
    }
};



</script>

<template>

    <Head title="Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Settings</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white p-6 rounded shadow-sm">
                    <div v-if="flashMessage" class="mb-4 p-4 bg-green-100 text-green-800 rounded shadow">
                        {{ flashMessage }}
                    </div>


                    <form @submit.prevent="form.post(route('settings.update'), {
                        replace: true
                    })">
                        <!-- Labor Settings -->
                        <h3 class="text-lg font-semibold mb-2">Labor Settings</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-medium mb-1">Estimated Labor Hours</label>
                                <input type="number" v-model="form.labor_hours" class="w-full border px-3 py-2 rounded"
                                    step="0.1" required />
                            </div>
                            <div>
                                <label class="block font-medium mb-1">Labor Cost Per Hour (£)</label>
                                <input type="number" v-model="form.labor_cost_per_hour"
                                    class="w-full border px-3 py-2 rounded" step="0.01" required />
                            </div>
                        </div>

                        <!-- Financial Settings -->
                        <h3 class="text-lg font-semibold mt-6 mb-2">Financial Settings</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-medium mb-1">Fixed Overheads (£)</label>
                                <input type="number" v-model="form.fixed_overheads"
                                    class="w-full border px-3 py-2 rounded" step="0.01" required />
                            </div>
                            <div>
                                <label class="block font-medium mb-1">Target Profit Margin (%)</label>
                                <input type="number" v-model="form.target_profit_margin"
                                    class="w-full border px-3 py-2 rounded" step="0.01" required />
                            </div>
                        </div>

                        <!-- AI Configuration -->
                        <h3 class="text-lg font-semibold mt-6 mb-2">AI Configuration</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-medium mb-1">LLM Provider</label>
                                <select v-model="form.llm_provider" class="w-full border px-3 py-2 rounded">
                                    <option value="">Select Provider</option>
                                    <option value="openai">OpenAI</option>
                                    <option value="huggingface">Hugging Face</option>
                                    <option value="selfhosted">Self Hosted</option>
                                </select>
                            </div>
                            <div v-if="form.llm_provider === 'openai'">
                                <label class="block font-medium mb-1">Model Name</label>
                                <select v-model="form.model_name" class="w-full border px-3 py-2 rounded">
                                    <option v-for="model in availableModels" :key="model" :value="model">
                                        {{ model }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-medium mb-1">API Key</label>
                                <input type="password" v-model="form.api_key" class="w-full border px-3 py-2 rounded" />
                            </div>

                            <div class="flex items-center space-x-3">
                                <button type="button" @click="checkConnection"
                                    class="mt-6 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                    Check Connection
                                </button>

                                <span v-if="connectionStatus === 'success'"
                                    class="mt-6 w-4 h-4 bg-green-500 rounded-full inline-block"
                                    title="Connected"></span>
                                <span v-if="connectionStatus === 'error'"
                                    class="mt-6 w-4 h-4 bg-red-500 rounded-full inline-block"
                                    title="Connection Failed"></span>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" :disabled="form.processing"
                                class="px-6 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                Save Settings
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
