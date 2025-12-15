<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
  favoriteLakes: {
    type: Array,
    required: true,
  },
});

const form = useForm({
  lake_id: props.favoriteLakes[0]?.id || '',
  trip_date: '',
  time_of_day: '',
  min_thickness_inches: '',
  avoid_slush: false,
  avoid_pressure_cracks: false,
  target_species: '',
  notes: '',
});

function submit() {
  form.post(route('trips.store'), {
    preserveScroll: true,
  });
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Plan a new trip" />

    <div class="max-w-3xl mx-auto p-4 space-y-4">
      <header class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold mb-1">
            Plan a new trip
          </h1>
          <p class="text-sm text-gray-500">
            Create a trip on one of your favorite lakes, with your own safety preferences.
          </p>
        </div>

        <Link
          :href="route('trips.index')"
          class="text-sm text-sky-600 hover:underline"
        >
          Back to trips
        </Link>
      </header>

      <div
        v-if="favoriteLakes.length === 0"
        class="text-sm text-gray-500"
      >
        You do not have any favorite lakes yet. Mark some lakes as favorites first, then come back here to plan a trip.
      </div>

      <form
        v-else
        @submit.prevent="submit"
        class="space-y-4 bg-white border rounded-lg p-4"
      >
        <div>
          <label class="block text-sm mb-1">
            Lake
          </label>
          <select
            v-model="form.lake_id"
            class="border rounded px-2 py-1 text-sm w-full max-w-xs"
            required
          >
            <option value="" disabled>
              Select a lake
            </option>
            <option
              v-for="lake in favoriteLakes"
              :key="lake.id"
              :value="lake.id"
            >
              {{ lake.name }}
              <span v-if="lake.region"> ({{ lake.region }})</span>
            </option>
          </select>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label class="block text-sm mb-1">
              Trip date
            </label>
            <input
              v-model="form.trip_date"
              type="date"
              class="border rounded px-2 py-1 text-sm w-full"
            />
          </div>

          <div>
            <label class="block text-sm mb-1">
              Time of day
            </label>
            <select
              v-model="form.time_of_day"
              class="border rounded px-2 py-1 text-sm w-full"
            >
              <option value="">
                Not specified
              </option>
              <option value="morning">
                Morning
              </option>
              <option value="afternoon">
                Afternoon
              </option>
              <option value="evening">
                Evening
              </option>
              <option value="night">
                Night
              </option>
            </select>
          </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
          <div>
            <label class="block text-sm mb-1">
              Minimum ice thickness (inches)
            </label>
            <input
              v-model="form.min_thickness_inches"
              type="number"
              min="0"
              max="60"
              step="0.5"
              class="border rounded px-2 py-1 text-sm w-full"
              placeholder="e.g. 4, 6, 8"
            />
            <p class="text-xs text-gray-500 mt-1">
              Your preferred minimum thickness for this trip. This does not replace real-world checks.
            </p>
          </div>

          <div class="flex flex-col justify-center gap-2 text-sm mt-2 md:mt-0">
            <label class="flex items-center gap-2">
              <input
                type="checkbox"
                v-model="form.avoid_slush"
              />
              Avoid trips when slush is reported
            </label>

            <label class="flex items-center gap-2">
              <input
                type="checkbox"
                v-model="form.avoid_pressure_cracks"
              />
              Avoid trips when pressure cracks are reported
            </label>
          </div>
        </div>

        <div>
          <label class="block text-sm mb-1">
            Target species
          </label>
          <input
            v-model="form.target_species"
            type="text"
            class="border rounded px-2 py-1 text-sm w-full"
            placeholder="walleye, perch, pike, panfish, etc."
          />
        </div>

        <div>
          <label class="block text-sm mb-1">
            Notes
          </label>
          <textarea
            v-model="form.notes"
            rows="3"
            class="border rounded px-2 py-1 text-sm w-full"
            placeholder="Parking access, buddies coming with, gear to bring, safety concerns, etc."
          ></textarea>
        </div>

        <div class="flex items-center gap-3">
          <button
            type="submit"
            class="px-4 py-2 rounded-md bg-sky-500 hover:bg-sky-400 text-sm text-white font-medium"
            :disabled="form.processing"
          >
            Save trip
          </button>

          <span
            v-if="form.recentlySuccessful"
            class="text-xs text-green-600"
          >
            Trip saved.
          </span>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
