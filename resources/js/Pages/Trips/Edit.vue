<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
  trip: {
    type: Object,
    required: true,
  },
  favoriteLakes: {
    type: Array,
    required: true,
  },
});

const form = useForm({
  lake_id: props.trip.lake_id,
  trip_date: props.trip.trip_date ?? '',
  time_of_day: props.trip.time_of_day ?? '',
  min_thickness_inches: props.trip.min_thickness_inches ?? '',
  avoid_slush: !!props.trip.avoid_slush,
  avoid_pressure_cracks: !!props.trip.avoid_pressure_cracks,
  target_species: props.trip.target_species ?? '',
  notes: props.trip.notes ?? '',
});

function submit() {
  form.put(route('trips.update', props.trip.id), {
    preserveScroll: true,
  });
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Edit trip" />

    <div class="max-w-3xl mx-auto p-4 space-y-4">
      <header class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold mb-1">
            Edit trip
          </h1>
          <p class="text-sm text-gray-500">
            Update your plan and safety preferences for this lake.
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
        You do not have any favorite lakes. Add favorites before editing trips.
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
              required
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
            placeholder="Parking access, buddies, gear, safety concerns."
          ></textarea>
        </div>

        <div class="flex items-center gap-3">
          <button
            type="submit"
            class="px-4 py-2 rounded-md bg-sky-500 hover:bg-sky-400 text-sm text-white font-medium"
            :disabled="form.processing"
          >
            Save changes
          </button>

          <span
            v-if="form.recentlySuccessful"
            class="text-xs text-green-600"
          >
            Trip updated.
          </span>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
