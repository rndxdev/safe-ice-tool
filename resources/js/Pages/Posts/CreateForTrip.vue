<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
  trip: {
    type: Object,
    required: true,
  },
  lake: {
    type: Object,
    default: null,
  },
});

const form = useForm({
  caption: '',
  // optional future fields:
  // is_public: false,
});

function submit() {
  form.post(route('trips.post.store', props.trip.id), {
    preserveScroll: true,
  });
}

function formatDate(dateStr) {
  if (!dateStr) return 'No date set';
  const parts = String(dateStr).split('-');
  if (parts.length !== 3) return String(dateStr);
  const [year, month, day] = parts;
  return `${Number(month)}/${Number(day)}/${year}`;
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Create trip post" />

    <div class="max-w-3xl mx-auto p-4 space-y-4">
      <header class="space-y-1">
        <Link :href="route('trips.index')" class="text-xs text-sky-600">
          Back to trips
        </Link>

        <h1 class="text-2xl font-semibold">
          Create trip post
        </h1>

        <p class="text-sm text-gray-500">
          Share a recap with the community. Keep it helpful, avoid exact private spot details if you don’t want crowds.
        </p>
      </header>

      <section class="border rounded-lg bg-white p-4 space-y-2">
        <div class="text-sm text-gray-500">
          Trip
        </div>

        <div class="text-base text-gray-900">
          <span v-if="lake">{{ lake.name }}</span>
          <span v-else>Trip</span>
        </div>

        <div class="text-xs text-gray-500">
          {{ formatDate(trip.trip_date) }}
          <span v-if="trip.time_of_day"> · {{ trip.time_of_day }}</span>
          <span v-if="lake?.region"> · {{ lake.region }}</span>
        </div>
      </section>

      <section class="border rounded-lg bg-white p-4">
        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm mb-1">
              Caption / recap
            </label>
            <textarea
              v-model="form.caption"
              rows="6"
              class="border rounded px-3 py-2 w-full text-sm"
              placeholder="How was the ice? Any slush? Pressure cracks? Fish caught? Access notes? Safety reminders?"
              required
            ></textarea>
            <div v-if="form.errors.caption" class="text-xs text-red-600 mt-1">
              {{ form.errors.caption }}
            </div>
          </div>

          <div class="flex items-center justify-between">
            <Link
              :href="route('trips.index')"
              class="text-xs text-gray-500 hover:underline"
            >
              Cancel
            </Link>

            <button
              type="submit"
              class="px-4 py-2 rounded-md bg-sky-500 hover:bg-sky-400 text-sm text-white"
              :disabled="form.processing"
            >
              Publish post
            </button>
          </div>
        </form>
      </section>

      <div class="text-xs text-gray-500 border rounded-lg bg-white p-3">
        Next step: after publishing, you can share the post publicly with a tokenized link.
      </div>
    </div>
  </AuthenticatedLayout>
</template>
