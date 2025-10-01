<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    contract: Object,
});

const csv = ref<File | null>(null);

function onFileChange(e) {
    csv.value = e.target.files[0] || null;
}

function upload() {
    if (!csv.value) return;
    const form = new FormData();
    form.append('csv', csv.value);
    router.post(route('manage.contract.upload-items', props.contract.id), form);
}
</script>

<template>
    <Head :title="contract.name" />
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold">{{ contract.name }}</h1>
            <p class="text-gray-600">For: {{ contract.user?.name || 'All users' }} | Status: {{ contract.status }}</p>
        </div>

        <div class="bg-white rounded shadow p-4 mb-6">
            <h2 class="text-lg font-semibold mb-2">Upload Items (CSV)</h2>
            <p class="text-sm text-gray-600 mb-2">Columns: sku, price, min_qty, pack_multiple</p>
            <input type="file" accept=".csv,text/csv" @change="onFileChange" />
            <button @click="upload" class="ml-2 px-3 py-1.5 rounded bg-blue-600 text-white">Upload</button>
        </div>

        <div class="bg-white rounded shadow">
            <div class="p-4 border-b font-medium">Items</div>
            <div v-if="!contract.items || contract.items.length === 0" class="p-6 text-center text-gray-600">No items</div>
            <div v-else class="divide-y">
                <div v-for="item in contract.items" :key="item.id" class="p-4 flex items-center justify-between">
                    <div>
                        <p class="font-medium">{{ item.product?.name || `Product #${item.product_id}` }}</p>
                        <p class="text-sm text-gray-600">Price: ${{ Number(item.price).toFixed(2) }} | Min: {{ item.min_qty }} | Pack: {{ item.pack_multiple }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

