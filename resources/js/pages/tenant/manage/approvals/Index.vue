<script setup>
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    approvals: Object,
});

function decide(id, decision) {
    router.post(route('manage.approvals.decide', id), { decision });
}
</script>

<template>
    <Head title="Approvals" />
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Approvals</h1>
        <div class="bg-white rounded shadow">
            <div class="p-4 border-b font-medium grid grid-cols-4 gap-2">
                <div>ID</div>
                <div>Order</div>
                <div>Status</div>
                <div>Actions</div>
            </div>
            <div v-for="a in approvals.data" :key="a.id" class="p-4 border-b grid grid-cols-4 gap-2 items-center">
                <div>#{{ a.id }}</div>
                <div>{{ a.order?.order_number }} - ${{ Number(a.order?.total || 0).toFixed(2) }}</div>
                <div>{{ a.state }}</div>
                <div class="flex gap-2">
                    <button @click="decide(a.id, 'approved')" class="px-3 py-1.5 rounded bg-green-600 text-white">Approve</button>
                    <button @click="decide(a.id, 'rejected')" class="px-3 py-1.5 rounded bg-red-600 text-white">Reject</button>
                </div>
            </div>
        </div>
    </div>
</template>

