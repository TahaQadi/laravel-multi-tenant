<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    contracts: Object,
});
</script>

<template>
    <Head title="Contracts" />
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Contracts</h1>
            <Link :href="route('manage.contract.create')" class="px-4 py-2 rounded bg-blue-600 text-white">New Contract</Link>
        </div>

        <div class="bg-white rounded shadow">
            <div v-for="contract in contracts.data" :key="contract.id" class="p-4 border-b flex items-center justify-between">
                <div>
                    <p class="font-medium">{{ contract.name }}</p>
                    <p class="text-sm text-gray-500">For: {{ contract.user?.name || 'All users' }} | Status: {{ contract.status }}</p>
                </div>
                <Link :href="route('manage.contract.show', contract.id)" class="px-3 py-1.5 rounded bg-gray-800 text-white">Open</Link>
            </div>
        </div>
    </div>
</template>

