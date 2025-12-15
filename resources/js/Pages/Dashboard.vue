<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  user: {
    type: Object,
    required: true,
  },
  favoriteLakes: {
    type: Array,
    required: true,
  },
  upcomingTrips: {
    type: Array,
    required: true,
  },
  recentReports: {
    type: Array,
    required: true,
  },
});

function formatDate(dateStr) {
  if (!dateStr) {
    return 'No date set';
  }

  const d = new Date(dateStr);
  if (Number.isNaN(d.getTime())) {
    return dateStr;
  }

  return d.toLocaleDateString();
}

function formatDateTime(dateStr) {
  if (!dateStr) {
    return '';
  }
  const d = new Date(dateStr);
  if (Number.isNaN(d.getTime())) {
    return dateStr;
  }
  return d.toLocaleString();
}

function getReportSummary(report) {
  const parts = [];

  if (report.thickness_inches != null) {
    parts.push(`${report.thickness_inches} in`);
  }
  if (report.ice_type) {
    parts.push(report.ice_type);
  }
  if (report.traffic_type) {
    parts.push(`traffic: ${report.traffic_type}`);
  }

  if (parts.length === 0) {
    return 'No details provided';
  }

  return parts.join(' · ');
}

function baseLakeUrl(lake) {
  if (typeof window === 'undefined') {
    return '';
  }
  return `${window.location.origin}${route('lakes.show', lake.slug)}`;
}

function shareToX(lake) {
  const url = encodeURIComponent(baseLakeUrl(lake));
  const text = encodeURIComponent(
    `Checking ice reports for ${lake.name} on Safe Ice Tool`,
  );

  const shareUrl = `https://twitter.com/intent/tweet?text=${text}&url=${url}`;

  if (typeof window !== 'undefined') {
    window.open(shareUrl, '_blank', 'noopener,noreferrer');
  }
}

function shareToFacebook(lake) {
  const url = encodeURIComponent(baseLakeUrl(lake));
  const shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;

  if (typeof window !== 'undefined') {
    window.open(shareUrl, '_blank', 'noopener,noreferrer');
  }
}

async function copyLakeLink(lake) {
  const url = baseLakeUrl(lake);
  if (!url) {
    return;
  }

  if (navigator && navigator.clipboard && navigator.clipboard.writeText) {
    try {
      await navigator.clipboard.writeText(url);
      // You could add a toast later; for now this is enough.
    } catch (e) {
      console.warn('Copy failed', e);
    }
  } else {
    console.warn('Clipboard API not available');
  }
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Dashboard" />

    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Welcome back, {{ user.name }}
          </h2>
          <p class="text-sm text-gray-500">
            Your lakes, trips, and the latest community ice reports in one place.
          </p>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

        <!-- Favorite lakes and sharing -->
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold">
              Favorite lakes
            </h3>
            <Link
              :href="route('lakes.index')"
              class="text-xs text-sky-600 hover:underline"
            >
              View all lakes
            </Link>
          </div>

          <div v-if="favoriteLakes.length === 0" class="text-sm text-gray-500">
            You have not favorited any lakes yet. Mark lakes you fish most often so you can plan trips and share them with friends.
          </div>

          <ul v-else class="space-y-2">
            <li
              v-for="lake in favoriteLakes"
              :key="lake.id"
              class="flex items-center justify-between border rounded-md p-3 hover:bg-gray-50"
            >
              <div>
                <Link
                  :href="route('lakes.show', lake.slug)"
                  class="text-sm text-sky-600 hover:underline"
                >
                  {{ lake.name }}
                </Link>
                <div v-if="lake.region" class="text-xs text-gray-500">
                  {{ lake.region }}
                </div>
              </div>

              <div class="flex gap-2 text-xs">
                <button
                  type="button"
                  class="px-2 py-1 border border-gray-300 rounded hover:bg-gray-100"
                  @click="shareToX(lake)"
                >
                  Share to X
                </button>
                <button
                  type="button"
                  class="px-2 py-1 border border-gray-300 rounded hover:bg-gray-100"
                  @click="shareToFacebook(lake)"
                >
                  Share to Facebook
                </button>
                <button
                  type="button"
                  class="px-2 py-1 border border-gray-300 rounded hover:bg-gray-100"
                  @click="copyLakeLink(lake)"
                >
                  Copy link
                </button>
              </div>
            </li>
          </ul>
        </div>

        <!-- Upcoming trips -->
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold">
              Upcoming trips
            </h3>
            <Link
              :href="route('trips.index')"
              class="text-xs text-sky-600 hover:underline"
            >
              View all trips
            </Link>
          </div>

          <div v-if="upcomingTrips.length === 0" class="text-sm text-gray-500">
            You have no trips planned yet. Use your favorite lakes to build a fishing plan for the season.
          </div>

          <ul v-else class="space-y-2">
            <li
              v-for="trip in upcomingTrips"
              :key="trip.id"
              class="border rounded-md p-3 hover:bg-gray-50"
            >
              <div class="flex justify-between items-baseline">
                <div>
                  <div class="text-sm text-gray-700">
                    <Link
                      v-if="trip.lake"
                      :href="route('lakes.show', trip.lake.slug)"
                      class="text-sky-600 hover:underline"
                    >
                      {{ trip.lake.name }}
                    </Link>
                    <span v-else>
                      Lake unavailable
                    </span>
                  </div>
                  <div
                    v-if="trip.lake && trip.lake.region"
                    class="text-xs text-gray-500"
                  >
                    {{ trip.lake.region }}
                  </div>
                </div>
                <div class="text-xs text-gray-600 text-right">
                  <div>
                    {{ formatDate(trip.trip_date) }}
                  </div>
                  <div v-if="trip.time_of_day">
                    {{ trip.time_of_day }}
                  </div>
                </div>
              </div>

              <div class="mt-2 text-xs text-gray-700 space-y-1">
                <div v-if="trip.min_thickness_inches">
                  Minimum thickness preference:
                  {{ trip.min_thickness_inches }} inches
                </div>

                <div v-if="trip.avoid_slush || trip.avoid_pressure_cracks">
                  Conditions to avoid:
                  <span v-if="trip.avoid_slush">
                    slush
                  </span>
                  <span v-if="trip.avoid_slush && trip.avoid_pressure_cracks">
                    ,
                  </span>
                  <span v-if="trip.avoid_pressure_cracks">
                    pressure cracks
                  </span>
                </div>

                <div v-if="trip.target_species">
                  Target species:
                  {{ trip.target_species }}
                </div>
              </div>

              <div v-if="trip.notes" class="mt-2 text-sm text-gray-800">
                {{ trip.notes }}
              </div>
            </li>
          </ul>
        </div>

        <!-- Recent reports on favorite lakes -->
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-lg font-semibold">
              Recent reports on your lakes
            </h3>
            <Link
              :href="route('lakes.index')"
              class="text-xs text-sky-600 hover:underline"
            >
              Explore lakes
            </Link>
          </div>

          <div v-if="recentReports.length === 0" class="text-sm text-gray-500">
            No recent community reports on your favorite lakes. Check back after anglers start logging conditions.
          </div>

          <ul v-else class="space-y-2">
            <li
              v-for="r in recentReports"
              :key="r.id"
              class="border rounded-md p-3 hover:bg-gray-50"
            >
              <div class="flex justify-between items-baseline">
                <div>
                  <div class="text-sm text-gray-700">
                    <Link
                      v-if="r.lake"
                      :href="route('lakes.show', r.lake.slug)"
                      class="text-sky-600 hover:underline"
                    >
                      {{ r.lake.name }}
                    </Link>
                    <span v-else>
                      Lake unavailable
                    </span>
                  </div>
                  <div v-if="r.lake && r.lake.region" class="text-xs text-gray-500">
                    {{ r.lake.region }}
                  </div>
                </div>
                <div class="text-xs text-gray-500">
                  {{ formatDateTime(r.created_at) }}
                </div>
              </div>

              <div class="mt-1 text-xs text-gray-700">
                {{ getReportSummary(r) }}
              </div>

              <div class="mt-1 text-xs text-gray-500">
                <span v-if="r.has_slush">Slush reported.</span>
                <span v-if="r.has_pressure_cracks">
                  <span v-if="r.has_slush"> </span>Pressure cracks present.
                </span>
              </div>

              <div v-if="r.notes" class="mt-2 text-sm text-gray-800">
                {{ r.notes }}
              </div>
            </li>
          </ul>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>
