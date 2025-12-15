<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  reports: {
    type: Array,
    required: true,
  },
});
</script>

<template>
  <AuthenticatedLayout>
    <Head title="My reports" />

    <div class="max-w-4xl mx-auto p-4 space-y-4">
      <header>
        <h1 class="text-2xl font-semibold mb-1">
          My ice reports
        </h1>
        <p class="text-sm text-gray-500">
          All reports you have submitted, across all lakes.
        </p>
      </header>

      <div v-if="reports.length === 0" class="text-sm text-gray-500">
        You have not submitted any reports yet. Go to the Lakes page to log your first one.
      </div>

      <ul v-else class="space-y-3">
        <li
          v-for="r in reports"
          :key="r.id"
          class="border rounded-lg bg-white p-3"
        >
          <div class="flex justify-between items-baseline">
            <div>
              <div class="text-sm text-gray-500">
                <Link
                  :href="route('lakes.show', r.lake.slug)"
                  class="text-sky-600 hover:underline"
                >
                  {{ r.lake.name }}
                </Link>
              </div>
              <div class="font-semibold">
                {{ r.thickness_inches }} inches
                <span v-if="r.ice_type"> ({{ r.ice_type }})</span>
              </div>
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

          <div class="mt-2 flex items-center gap-3 text-xs">
  <span class="text-gray-600">
    Votes: Up {{ r.upvotes }} · Down {{ r.downvotes }}
  </span>

  <span
    v-if="r.is_hidden"
    class="px-2 py-0.5 rounded bg-red-50 text-red-700 border border-red-200"
  >
    Hidden from public view
  </span>
</div>


        </li>
      </ul>
    </div>
  </AuthenticatedLayout>
</template>
