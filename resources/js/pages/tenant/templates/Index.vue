<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    templates: Object,
});
</script>

<template>
    <Head title="Templates" />
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Order Templates</h1>
        <div class="bg-white shadow rounded-lg">
            <div class="divide-y">
                <div v-for="template in templates.data" :key="template.id" class="p-4 flex items-center justify-between">
                    <div>
                        <p class="font-medium">{{ template.name }}</p>
                        <p class="text-sm text-gray-500">{{ template.items_count }} items</p>
                    </div>
                    <div class="flex gap-2">
                        <Link :href="route('templates.show', template.id)" class="px-3 py-1.5 rounded bg-blue-600 text-white">View</Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</template>

