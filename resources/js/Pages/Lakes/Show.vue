<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
  lake: {
    type: Object,
    required: true,
  },
  reports: {
    type: Array,
    required: true,
  },
});

const mapRef = ref(null);
const mapInstance = ref(null);
const userPinMarker = ref(null);

const form = useForm({
  lat: null,
  lng: null,
  thickness_inches: '',
  ice_type: '',
  traffic_type: '',
  has_slush: false,
  has_pressure_cracks: false,
  notes: '',
});

function setPin(lat, lng) {
  form.lat = Number(lat);
  form.lng = Number(lng);

  if (!mapInstance.value) {
    return;
  }

  const ll = [form.lat, form.lng];

  if (userPinMarker.value) {
    userPinMarker.value.setLatLng(ll);
  } else {
    userPinMarker.value = L.marker(ll, { draggable: true }).addTo(mapInstance.value);
    userPinMarker.value.on('dragend', (e) => {
      const pos = e.target.getLatLng();
      form.lat = Number(pos.lat);
      form.lng = Number(pos.lng);
    });
  }
}

function clearPin() {
  form.lat = null;
  form.lng = null;

  if (mapInstance.value && userPinMarker.value) {
    mapInstance.value.removeLayer(userPinMarker.value);
    userPinMarker.value = null;
  }
}

function submit() {
  if (form.lat === null || form.lng === null) {
    return;
  }

  form.post(route('reports.store', props.lake.slug), {
    preserveScroll: true,
    onSuccess: () => {
      clearPin();
      form.reset(
        'thickness_inches',
        'ice_type',
        'traffic_type',
        'has_slush',
        'has_pressure_cracks',
        'notes',
      );
    },
  });
}

function vote(reportId, direction) {
  const routeName = direction === 'up' ? 'reports.upvote' : 'reports.downvote';

  router.post(
    route(routeName, reportId),
    {},
    {
      preserveScroll: true,
      preserveState: true,
    },
  );
}

onMounted(() => {
  const lat = Number(props.lake.lat) || 0;
  const lng = Number(props.lake.lng) || 0;

  const map = L.map('lake-map').setView([lat, lng], 11);
  mapInstance.value = map;

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
    attribution: 'Map data © OpenStreetMap contributors',
  }).addTo(map);

  L.circleMarker([lat, lng], {
    radius: 6,
    weight: 2,
    color: '#0ea5e9',
    fillColor: '#0ea5e9',
    fillOpacity: 0.6,
  })
    .addTo(map)
    .bindPopup(props.lake.name);

  props.reports.forEach((r) => {
    if (r.lat != null && r.lng != null) {
      const rLat = Number(r.lat);
      const rLng = Number(r.lng);

      if (!Number.isNaN(rLat) && !Number.isNaN(rLng)) {
        L.circleMarker([rLat, rLng], {
          radius: 5,
          weight: 1,
          color: '#22c55e',
          fillColor: '#22c55e',
          fillOpacity: 0.7,
        })
          .addTo(map)
          .bindPopup(`${r.thickness_inches} in${r.ice_type ? ` (${r.ice_type})` : ''}`);
      }
    }
  });

  map.on('click', (e) => {
    setPin(e.latlng.lat, e.latlng.lng);
  });
});
</script>

<template>
  <AuthenticatedLayout>
    <Head :title="lake.name" />

    <div class="max-w-4xl mx-auto p-4 space-y-6">
      <div>
        <Link :href="route('lakes.index')" class="text-xs text-sky-600">
          Back to lakes
        </Link>
        <h1 class="text-2xl font-semibold mt-2">
          {{ lake.name }}
        </h1>
        <p v-if="lake.region" class="text-sm text-gray-500">
          {{ lake.region }}
        </p>
        <p class="text-xs text-gray-500 mt-1">
          Community-powered ice thickness reports. Always confirm conditions in person before going on the ice.
        </p>
      </div>

      <section>
        <h2 class="text-lg font-medium mb-3">
          Lake map
        </h2>

        <div
          id="lake-map"
          class="h-64 w-full rounded-lg border border-gray-300 overflow-hidden"
        ></div>

        <div class="mt-2 flex flex-wrap gap-2 text-xs text-gray-600">
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

          <div class="w-full text-gray-500">
            Click the map to drop your report pin. You can drag the pin to adjust.
          </div>
        </div>
      </section>

      <section class="border rounded-lg p-4 bg-white">
        <h2 class="text-lg font-medium mb-3">
          Submit an ice report
        </h2>

        <div
          v-if="form.lat === null || form.lng === null"
          class="mb-3 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded p-3"
        >
          Drop a pin on the map first so people know where the measurement came from.
        </div>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm mb-1">Thickness (inches)</label>
            <input
              v-model="form.thickness_inches"
              type="number"
              min="0"
              max="60"
              step="0.5"
              class="border rounded px-3 py-2 w-40 text-base"
              required
            />
          </div>

          <div class="grid gap-3 md:grid-cols-2">
            <div>
              <label class="block text-sm mb-1">Ice type</label>
              <input
                v-model="form.ice_type"
                type="text"
                class="border rounded px-3 py-2 w-full text-base"
                placeholder="clear, white, slush"
              />
            </div>

            <div>
              <label class="block text-sm mb-1">Traffic observed</label>
              <input
                v-model="form.traffic_type"
                type="text"
                class="border rounded px-3 py-2 w-full text-base"
                placeholder="foot, atv, sled, truck"
              />
            </div>
          </div>

          <div class="flex flex-wrap gap-4 text-base">
            <label class="flex items-center gap-2">
              <input type="checkbox" v-model="form.has_slush" />
              Slush present
            </label>
            <label class="flex items-center gap-2">
              <input type="checkbox" v-model="form.has_pressure_cracks" />
              Pressure cracks present
            </label>
          </div>

          <div>
            <label class="block text-sm mb-1">Notes</label>
            <textarea
              v-model="form.notes"
              rows="3"
              class="border rounded px-3 py-2 w-full text-base"
              placeholder="Cracks near inlet, variable thickness, shoreline heaved, etc."
            ></textarea>
          </div>

          <button
            type="submit"
            class="px-4 py-3 rounded-md bg-sky-500 hover:bg-sky-400 text-base text-white font-semibold disabled:opacity-50"
            :disabled="form.processing || form.lat === null || form.lng === null"
          >
            Submit report
          </button>
        </form>
      </section>

      <section>
        <h2 class="text-lg font-medium mb-3">
          Recent reports
        </h2>

        <div v-if="reports.length === 0" class="text-sm text-gray-500">
          No reports yet. Be the first to log one for this lake.
        </div>

        <ul v-else class="space-y-2">
          <li
            v-for="r in reports"
            :key="r.id"
            class="border rounded-lg p-3 bg-white"
          >
            <div class="flex justify-between items-baseline">
              <div class="font-semibold">
                {{ r.thickness_inches }} inches
                <span v-if="r.ice_type"> ({{ r.ice_type }})</span>
              </div>
              <div class="text-xs text-gray-500">
                {{ new Date(r.created_at).toLocaleString() }}
              </div>
            </div>

            <div class="text-xs text-gray-500 mt-1">
              Traffic:
              <span v-if="r.traffic_type">{{ r.traffic_type }}</span>
              <span v-else>not specified</span>
            </div>

            <div class="text-xs text-gray-500 mt-1">
              <span v-if="r.has_slush">Slush reported.</span>
              <span v-if="r.has_pressure_cracks">
                <span v-if="r.has_slush"> </span>Pressure cracks present.
              </span>
            </div>

            <div v-if="r.notes" class="mt-2 text-sm">
              {{ r.notes }}
            </div>

            <div class="mt-3 flex items-center gap-3 text-xs text-gray-600">
              <button
                type="button"
                class="px-2 py-1 border border-gray-300 rounded hover:bg-gray-50"
                @click="vote(r.id, 'up')"
              >
                Upvote ({{ r.upvotes }})
              </button>

              <button
                type="button"
                class="px-2 py-1 border border-gray-300 rounded hover:bg-gray-50"
                @click="vote(r.id, 'down')"
              >
                Downvote ({{ r.downvotes }})
              </button>
            </div>
          </li>
        </ul>
      </section>
    </div>
  </AuthenticatedLayout>
</template>
