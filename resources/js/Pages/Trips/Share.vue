<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  trip: {
    type: Object,
    required: true,
  },
  lake: {
    type: Object,
    default: null,
  },
  safety: {
    type: Object,
    default: null,
  },
  shareUrl: {
    type: String,
    required: true,
  },
  canLogin: {
    type: Boolean,
    required: true,
  },
  canRegister: {
    type: Boolean,
    required: true,
  },
});

const formatDate = (dateStr) => {
  if (!dateStr) return 'No date set';

  // Expect YYYY-MM-DD
  const parts = String(dateStr).split('-');
  if (parts.length !== 3) return dateStr;

  const [year, month, day] = parts;
  return `${Number(month)}/${Number(day)}/${year}`;
};

function badgeClass(score) {
  if (score === null || score === undefined) {
    return 'text-gray-700 bg-gray-100 border border-gray-200';
  }
  if (score >= 75) {
    return 'text-green-700 bg-green-50 border border-green-200';
  }
  if (score >= 50) {
    return 'text-amber-700 bg-amber-50 border border-amber-200';
  }
  return 'text-red-700 bg-red-50 border border-red-200';
}

function copyLink() {
  const url = props.shareUrl;

  if (navigator.clipboard?.writeText) {
    navigator.clipboard.writeText(url).then(
      () => alert('Link copied.'),
      () => prompt('Copy this link:', url),
    );
    return;
  }

  prompt('Copy this link:', url);
}

function shareNative() {
  const url = props.shareUrl;
  const lakeName = props.lake?.name ?? 'a lake';
  const date = props.trip?.trip_date ? formatDate(props.trip.trip_date) : 'soon';

  if (!navigator.share) {
    copyLink();
    return;
  }

  navigator
    .share({
      title: 'Safe Ice Tool trip plan',
      text: `Trip plan for ${lakeName} on ${date}.`,
      url,
    })
    .catch(() => {
      // user cancelled or share failed; no-op
    });
}

function shareToX() {
  const url = props.shareUrl;
  const lakeName = props.lake?.name ?? 'a lake';
  const date = props.trip?.trip_date ? formatDate(props.trip.trip_date) : 'soon';
  const text = `Trip plan: ${lakeName} on ${date}. ${url}`;
  window.open(`https://x.com/intent/tweet?text=${encodeURIComponent(text)}`, '_blank', 'noopener,noreferrer');
}
</script>

<template>
  <GuestLayout>
    <Head title="Shared trip" />

    <div class="w-full sm:max-w-2xl">
      <div class="bg-white border rounded-lg shadow-sm p-5 space-y-4">
        <header class="space-y-1">
          <div class="text-xs text-gray-500">
            Shared trip plan
          </div>

          <h1 class="text-xl font-semibold text-gray-900">
            <span v-if="lake">{{ lake.name }}</span>
            <span v-else>Trip plan</span>
          </h1>

          <div v-if="lake?.region" class="text-sm text-gray-500">
            {{ lake.region }}
          </div>
        </header>

        <div class="grid gap-3 sm:grid-cols-2 text-sm">
          <div class="border rounded-lg p-3">
            <div class="text-xs text-gray-500">Planned date</div>
            <div class="font-medium text-gray-900">
              {{ formatDate(trip.trip_date) }}
            </div>
            <div v-if="trip.time_of_day" class="text-xs text-gray-500 mt-1">
              {{ trip.time_of_day }}
            </div>
          </div>

          <div class="border rounded-lg p-3">
            <div class="text-xs text-gray-500">Safety preferences</div>

            <div v-if="trip.min_thickness_inches" class="mt-1">
              Min thickness: <span class="font-medium">{{ trip.min_thickness_inches }}</span> in
            </div>
            <div v-else class="mt-1 text-gray-600">
              No minimum thickness set
            </div>

            <div class="mt-1 text-gray-700" v-if="trip.avoid_slush || trip.avoid_pressure_cracks">
              Avoid:
              <span v-if="trip.avoid_slush">slush</span>
              <span v-if="trip.avoid_slush && trip.avoid_pressure_cracks">, </span>
              <span v-if="trip.avoid_pressure_cracks">pressure cracks</span>
            </div>
            <div class="mt-1 text-gray-600" v-else>
              No avoid conditions set
            </div>
          </div>
        </div>

        <div v-if="trip.target_species" class="text-sm">
          <div class="text-xs text-gray-500">Target species</div>
          <div class="text-gray-900">{{ trip.target_species }}</div>
        </div>

        <div v-if="trip.notes" class="text-sm">
          <div class="text-xs text-gray-500">Notes</div>
          <div class="text-gray-900 whitespace-pre-line">{{ trip.notes }}</div>
        </div>

        <div v-if="safety" class="border rounded-lg p-3">
          <div class="flex items-center justify-between gap-3">
            <div>
              <div class="text-xs text-gray-500">Lake safety snapshot</div>
              <div class="text-sm text-gray-900 mt-0.5">
                {{ safety.label }}
              </div>
            </div>

            <span
              class="px-2 py-0.5 rounded text-[11px] border"
              :class="badgeClass(safety.score)"
            >
              Score: {{ safety.score ?? 'N/A' }}
            </span>
          </div>

          <div class="text-xs text-gray-500 mt-2">
            Always verify conditions in person. Ice conditions can change fast.
          </div>
        </div>

        <div class="flex flex-wrap gap-2">
          <button
            type="button"
            class="px-3 py-2 rounded-md bg-sky-500 hover:bg-sky-400 text-sm text-white"
            @click="shareNative"
          >
            Share
          </button>

          <button
            type="button"
            class="px-3 py-2 rounded-md border border-gray-300 hover:bg-gray-50 text-sm"
            @click="copyLink"
          >
            Copy link
          </button>

          <button
            type="button"
            class="px-3 py-2 rounded-md border border-gray-300 hover:bg-gray-50 text-sm"
            @click="shareToX"
          >
            Share to X
          </button>

          <Link
            v-if="lake?.slug"
            :href="route('welcome')"
            class="px-3 py-2 rounded-md border border-gray-300 hover:bg-gray-50 text-sm"
          >
            Learn more
          </Link>
        </div>
      </div>

      <div class="mt-4 border rounded-lg bg-white p-4 text-sm text-gray-700 space-y-2">
        <div class="font-medium text-gray-900">
          Want your own trip plans and ice reports?
        </div>

        <div>
          Safe Ice Tool is community-powered. Create an account to favorite lakes, plan trips, and share safely.
        </div>

        <div class="flex gap-2 pt-1">
          <Link
            v-if="canLogin"
            :href="route('login')"
            class="px-3 py-2 rounded-md border border-gray-300 hover:bg-gray-50 text-sm"
          >
            Log in
          </Link>

          <Link
            v-if="canRegister"
            :href="route('register')"
            class="px-3 py-2 rounded-md bg-gray-900 hover:bg-gray-800 text-sm text-white"
          >
            Create account
          </Link>
        </div>
      </div>
    </div>
  </GuestLayout>
</template>
