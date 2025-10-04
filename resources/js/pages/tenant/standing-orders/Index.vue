<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    standingOrders: Object,
});

const name = ref('');
const schedule = ref('0 9 * * 1'); // default weekly Mondays 09:00
const items = ref([{ product_id: '', qty: 1 }]);

function addItemRow() {
    items.value.push({ product_id: '', qty: 1 });
}

function createStandingOrder() {
    router.post(route('standing-orders.store'), {
        name: name.value,
        schedule_cron: schedule.value,
        items: items.value,
    });
}

function runNow(id) {
    router.post(route('standing-orders.run', id));
}
</script>

<template>
    <Head title="Standing Orders" />
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Standing Orders</h1>

        <div class="bg-white rounded shadow p-4 mb-8">
            <h2 class="text-lg font-semibold mb-4">Create New</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600">Name</label>
                    <input v-model="name" class="border rounded px-3 py-2 w-full" />
                </div>
                <div>
                    <label class="block text-sm text-gray-600">Cron</label>
                    <input v-model="schedule" class="border rounded px-3 py-2 w-full" />
                </div>
            </div>

            <div class="mt-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-medium">Items</h3>
                    <button @click="addItemRow" class="px-3 py-1.5 rounded bg-gray-800 text-white">Add Item</button>
                </div>
                <div class="space-y-2">
                    <div v-for="(row, idx) in items" :key="idx" class="grid grid-cols-2 gap-2">
                        <input v-model="row.product_id" placeholder="Product ID" class="border rounded px-3 py-2" />
                        <input v-model.number="row.qty" type="number" min="1" class="border rounded px-3 py-2" />
                    </div>
                </div>
            </div>

            <div class="mt-4 flex justify-end">
                <button @click="createStandingOrder" class="px-4 py-2 rounded bg-blue-600 text-white">Create</button>
            </div>
        </div>

        <div class="bg-white rounded shadow">
            <div class="p-4 border-b font-medium grid grid-cols-4 gap-2">
                <div>Name</div>
                <div>Schedule</div>
                <div>Items</div>
                <div>Actions</div>
            </div>
            <div v-for="so in standingOrders.data" :key="so.id" class="p-4 border-b grid grid-cols-4 gap-2 items-center">
                <div>{{ so.name }}</div>
                <div>{{ so.schedule_cron }}</div>
                <div>{{ so.items_count }}</div>
                <div>
                    <button @click="runNow(so.id)" class="px-3 py-1.5 rounded bg-green-600 text-white">Run Now</button>
                </div>
            </div>
        </div>
    </div>
</template>

