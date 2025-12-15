<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
  states: {
    type: Array,
    required: true,
  },
});

function toggleFavorite(lake) {
  router.post(
    route('lakes.favorite', lake.slug),
    {},
    {
      preserveScroll: true,
      preserveState: true,
    },
  );
}

function stabilityBadgeClass(score) {
  if (score === null || score === undefined) {
    return 'text-gray-700 bg-gray-100 border border-gray-200 px-2 py-0.5 rounded text-[11px]';
  }
  if (score >= 75) {
    return 'text-green-700 bg-green-50 border border-green-200 px-2 py-0.5 rounded text-[11px]';
  }
  if (score >= 50) {
    return 'text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded text-[11px]';
  }
  return 'text-red-700 bg-red-50 border border-red-200 px-2 py-0.5 rounded text-[11px]';
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Lakes" />

    <div class="max-w-5xl mx-auto p-4 space-y-4">
      <header class="space-y-1">
        <h1 class="text-2xl font-semibold">
          Lakes
        </h1>
        <p class="text-sm text-gray-600">
          Browse by state and county. Save favorites for quick access.
        </p>
      </header>

      <div v-if="states.length === 0" class="text-sm text-gray-500">
        No lakes found.
      </div>

      <div v-else class="space-y-6">
        <section
          v-for="state in states"
          :key="state.name || 'unknown-state'"
          class="border rounded-xl bg-white shadow-sm"
        >
          <div class="border-b bg-gray-50 px-4 py-3">
            <h2 class="text-lg font-semibold">
              {{ state.name || 'Unknown state' }}
            </h2>
          </div>

          <div class="divide-y">
            <div
              v-for="county in state.counties"
              :key="county.name || 'unknown-county'"
              class="px-4 py-3"
            >
              <h3 class="text-base font-medium mb-2">
                {{ county.name || 'Unknown county' }}
              </h3>

              <ul class="space-y-1">
                <li
                  v-for="lake in county.lakes"
                  :key="lake.id"
                  class="flex items-center justify-between rounded-lg px-3 py-2 hover:bg-gray-50"
                >
                  <div>
                    <Link
                      :href="route('lakes.show', lake.slug)"
                      class="block"
                    >
                      <div class="text-base font-medium">
                        {{ lake.name }}
                      </div>
                      <div v-if="lake.region" class="text-xs text-gray-500">
                        {{ lake.region }}
                      </div>
                    </Link>

                    <div v-if="lake.safety" class="mt-1">
                      <span :class="stabilityBadgeClass(lake.safety.score)">
                        {{ lake.safety.label }}
                      </span>
                    </div>
                  </div>

                  <button
                    type="button"
                    class="p-1 rounded-full hover:bg-gray-100"
                    @click="toggleFavorite(lake)"
                    :aria-label="lake.is_favorite ? 'Remove from favorites' : 'Add to favorites'"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      viewBox="0 0 24 24"
                      class="h-5 w-5"
                      :class="lake.is_favorite ? 'text-red-500' : 'text-gray-400'"
                      fill="currentColor"
                    >
                      <path
                        d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5
                           2 6 3.99 4 6.5 4
                           8.04 4 9.54 4.81 10.35 6.08
                           11.16 4.81 12.66 4 14.2 4
                           16.71 4 18.7 6 18.7 8.5
                           18.7 12.28 15.3 15.36 13.45 17.03
                           L12 21.35z"
                      />
                    </svg>
                  </button>
                </li>
              </ul>
            </div>
          </div>
        </section>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
