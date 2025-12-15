<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const mapInstance = ref(null);
const marker = ref(null);

const form = useForm({
  name: '',
  lat: null,
  lng: null,
});

function setPin(lat, lng) {
  form.lat = Number(lat);
  form.lng = Number(lng);

  if (!mapInstance.value) {
    return;
  }

  const ll = [form.lat, form.lng];

  if (marker.value) {
    marker.value.setLatLng(ll);
  } else {
    marker.value = L.marker(ll, { draggable: true }).addTo(mapInstance.value);

    marker.value.on('dragend', (e) => {
      const pos = e.target.getLatLng();
      form.lat = Number(pos.lat);
      form.lng = Number(pos.lng);
    });
  }
}

function clearPin() {
  form.lat = null;
  form.lng = null;

  if (mapInstance.value && marker.value) {
    mapInstance.value.removeLayer(marker.value);
    marker.value = null;
  }
}

function submit() {
  if (form.lat === null || form.lng === null) {
    return;
  }

  form.post(route('lakes.store'), {
    preserveScroll: true,
  });
}

onMounted(() => {
  const map = L.map('create-lake-map').setView([46.5, -87.5], 7);
  mapInstance.value = map;

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
    attribution: 'Map data © OpenStreetMap contributors',
  }).addTo(map);

  map.on('click', (e) => {
    setPin(e.latlng.lat, e.latlng.lng);
  });
});
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Add a lake" />

    <div class="max-w-4xl mx-auto p-4 space-y-6">
      <div>
        <Link :href="route('lakes.index')" class="text-xs text-sky-600">
          Back to lakes
        </Link>
        <h1 class="text-2xl font-semibold mt-2">
          Add a lake
        </h1>
        <p class="text-sm text-gray-600 mt-1">
          Click the map to drop a lake pin. Name it, then you can add the first ice report.
        </p>
      </div>

      <section class="space-y-3">
        <div
          id="create-lake-map"
          class="h-80 w-full rounded-lg border border-gray-300 overflow-hidden"
        ></div>

        <div class="flex flex-wrap gap-2 text-xs text-gray-600">
          <div v-if="form.lat !== null && form.lng !== null" class="px-2 py-1 bg-gray-50 border rounded">
            Pin: {{ form.lat.toFixed(5) }}, {{ form.lng.toFixed(5) }}
          </div>

          <button
            v-if="form.lat !== null && form.lng !== null"
            type="button"
            class="px-2 py-1 border border-gray-300 rounded hover:bg-gray-50"
            @click="clearPin"
          >
            Clear pin
          </button>
        </div>
      </section>

      <section class="border rounded-lg p-4 bg-white space-y-4">
        <div>
          <label class="block text-sm mb-1">Lake name</label>
          <input
            v-model="form.name"
            type="text"
            class="border rounded px-3 py-2 w-full text-base"
            placeholder="Witch Lake"
            required
          />
          <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">
            {{ form.errors.name }}
          </div>
        </div>

        <div v-if="form.lat === null || form.lng === null" class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded p-3">
          Drop a pin on the map before creating the lake.
        </div>

        <button
          type="button"
          class="px-4 py-3 rounded-md bg-sky-500 hover:bg-sky-400 text-base text-white font-semibold disabled:opacity-50"
          :disabled="form.processing || !form.name || form.lat === null || form.lng === null"
          @click="submit"
        >
          Create lake
        </button>
      </section>
    </div>
  </AuthenticatedLayout>
</template>
