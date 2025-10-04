<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    template: Object,
});

const newItemProductId = ref('');
const newItemQty = ref(1);

function addItem() {
    router.post(route('templates.items.add', props.template.id), {
        product_id: newItemProductId.value,
        default_qty: newItemQty.value,
    }, { preserveScroll: true });
}

function applyToCart() {
    router.post(route('templates.apply', props.template.id), {}, { preserveScroll: true });
}
</script>

<template>
    <Head :title="template.name" />
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">{{ template.name }}</h1>
            <button @click="applyToCart" class="px-4 py-2 rounded bg-blue-600 text-white">Add all to cart</button>
        </div>

        <div class="bg-white rounded shadow p-4 mb-6">
            <div class="flex gap-2 items-end">
                <div class="flex flex-col">
                    <label class="text-sm text-gray-600">Product ID</label>
                    <input v-model="newItemProductId" class="border rounded px-2 py-1" placeholder="Enter product id" />
                </div>
                <div class="flex flex-col">
                    <label class="text-sm text-gray-600">Default Qty</label>
                    <input type="number" min="1" v-model.number="newItemQty" class="border rounded px-2 py-1 w-28" />
                </div>
                <button @click="addItem" class="px-3 py-1.5 rounded bg-gray-800 text-white">Add Item</button>
            </div>
        </div>

        <div class="bg-white rounded shadow">
            <div v-if="template.items.length === 0" class="p-6 text-center text-gray-600">
                No items yet.
            </div>
            <div v-else class="divide-y">
                <div v-for="item in template.items" :key="item.id" class="p-4 flex items-center justify-between">
                    <div>
                        <p class="font-medium">{{ item.product?.name || `Product #${item.product_id}` }}</p>
                        <p class="text-sm text-gray-500">Default Qty: {{ item.default_qty }}</p>
                    </div>
                    <Link :href="route('templates.items.remove', { template: template.id, item: item.id })" as="button" method="delete" class="px-3 py-1.5 rounded bg-red-600 text-white">Remove</Link>
                </div>
            </div>
        </div>
    </div>
</template>

