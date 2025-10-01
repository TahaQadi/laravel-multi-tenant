<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: Array,
});

const name = ref('');
const userId = ref('');
const startAt = ref('');
const endAt = ref('');
const status = ref('active');

function submit() {
    router.post(route('manage.contract.store'), {
        name: name.value,
        user_id: userId.value || null,
        start_at: startAt.value,
        end_at: endAt.value || null,
        status: status.value,
    });
}
</script>

<template>
    <Head title="New Contract" />
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Create Contract</h1>

        <div class="bg-white rounded shadow p-6 space-y-4">
            <div>
                <label class="block text-sm text-gray-600">Name</label>
                <input v-model="name" class="border rounded px-3 py-2 w-full" />
            </div>

            <div>
                <label class="block text-sm text-gray-600">User (optional)</label>
                <select v-model="userId" class="border rounded px-3 py-2 w-full">
                    <option value="">All users</option>
                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600">Start At</label>
                    <input type="date" v-model="startAt" class="border rounded px-3 py-2 w-full" />
                </div>
                <div>
                    <label class="block text-sm text-gray-600">End At (optional)</label>
                    <input type="date" v-model="endAt" class="border rounded px-3 py-2 w-full" />
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-600">Status</label>
                <select v-model="status" class="border rounded px-3 py-2 w-full">
                    <option value="active">Active</option>
                    <option value="draft">Draft</option>
                    <option value="expired">Expired</option>
                </select>
            </div>

            <div class="flex justify-end">
                <button @click="submit" class="px-4 py-2 rounded bg-blue-600 text-white">Create</button>
            </div>
        </div>
    </div>
</template>

