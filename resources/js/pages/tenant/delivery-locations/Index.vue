<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    locations: Object,
});

const name = ref('');
const address = ref('');
const city = ref('');
const state = ref('');
const country = ref('');
const zipcode = ref('');

function save() {
    router.post(route('delivery-locations.store'), {
        name: name.value,
        address: address.value,
        city: city.value,
        state: state.value,
        country: country.value,
        zipcode: zipcode.value,
    });
}

function remove(id) {
    router.delete(route('delivery-locations.destroy', id));
}
</script>

<template>
    <Head title="Delivery Locations" />
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Delivery Locations</h1>

        <div class="bg-white rounded shadow p-4 mb-8 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm text-gray-600">Name</label>
                <input v-model="name" class="border rounded px-3 py-2 w-full" />
            </div>
            <div>
                <label class="text-sm text-gray-600">Address</label>
                <input v-model="address" class="border rounded px-3 py-2 w-full" />
            </div>
            <div>
                <label class="text-sm text-gray-600">City</label>
                <input v-model="city" class="border rounded px-3 py-2 w-full" />
            </div>
            <div>
                <label class="text-sm text-gray-600">State</label>
                <input v-model="state" class="border rounded px-3 py-2 w-full" />
            </div>
            <div>
                <label class="text-sm text-gray-600">Country</label>
                <input v-model="country" class="border rounded px-3 py-2 w-full" />
            </div>
            <div>
                <label class="text-sm text-gray-600">Zipcode</label>
                <input v-model="zipcode" class="border rounded px-3 py-2 w-full" />
            </div>
            <div class="md:col-span-2 flex justify-end">
                <button @click="save" class="px-4 py-2 rounded bg-blue-600 text-white">Save</button>
            </div>
        </div>

        <div class="bg-white rounded shadow">
            <div v-for="loc in locations.data" :key="loc.id" class="p-4 border-b flex items-center justify-between">
                <div>
                    <p class="font-medium">{{ loc.name }}</p>
                    <p class="text-sm text-gray-600">{{ loc.address }}, {{ loc.city }}, {{ loc.state }} {{ loc.zipcode }}</p>
                </div>
                <button @click="remove(loc.id)" class="px-3 py-1.5 rounded bg-red-600 text-white">Remove</button>
            </div>
        </div>
    </div>
</template>

