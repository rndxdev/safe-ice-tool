<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

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

const copiedLakeId = ref(null);

// State DNR and fishing resources - keyed by state abbreviation
const stateResources = {
  MN: {
    name: 'Minnesota',
    dnr: 'https://www.dnr.state.mn.us/ice_safety/index.html',
    fishing: 'https://www.dnr.state.mn.us/fishing/index.html',
    regulations: 'https://www.dnr.state.mn.us/regulations/fishing/index.html',
  },
  WI: {
    name: 'Wisconsin',
    dnr: 'https://dnr.wisconsin.gov/topic/fishing/ice',
    fishing: 'https://dnr.wisconsin.gov/topic/fishing',
    regulations: 'https://dnr.wisconsin.gov/topic/fishing/regulations',
  },
  MI: {
    name: 'Michigan',
    dnr: 'https://www.michigan.gov/dnr/things-to-do/fishing/ice-fishing',
    fishing: 'https://www.michigan.gov/dnr/things-to-do/fishing',
    regulations: 'https://www.michigan.gov/dnr/managing-resources/fisheries/regulations',
  },
  NY: {
    name: 'New York',
    dnr: 'https://www.dec.ny.gov/outdoor/7733.html',
    fishing: 'https://www.dec.ny.gov/outdoor/fishing.html',
    regulations: 'https://www.dec.ny.gov/outdoor/7894.html',
  },
  PA: {
    name: 'Pennsylvania',
    dnr: 'https://www.fishandboat.com/Fish/Ice-Fishing',
    fishing: 'https://www.fishandboat.com/Fish',
    regulations: 'https://www.fishandboat.com/Fish/Regulations',
  },
  OH: {
    name: 'Ohio',
    dnr: 'https://ohiodnr.gov/discover-and-learn/safety-conservation/about-ODNR/wildlife/ice-fishing-safety',
    fishing: 'https://ohiodnr.gov/wps/portal/gov/odnr/discover-and-learn/activities/fishing',
    regulations: 'https://ohiodnr.gov/wps/portal/gov/odnr/buy-and-apply/fishing-hunting-boating-licenses/fishing-licenses-permits-background',
  },
  IN: {
    name: 'Indiana',
    dnr: 'https://www.in.gov/dnr/fish-and-wildlife/fishing/ice-fishing/',
    fishing: 'https://www.in.gov/dnr/fish-and-wildlife/fishing/',
    regulations: 'https://www.in.gov/dnr/fish-and-wildlife/files/fw-Fish_Regs.pdf',
  },
  IL: {
    name: 'Illinois',
    dnr: 'https://www.dnr.illinois.gov/recreation/fishing/Pages/IceFishing.aspx',
    fishing: 'https://www.dnr.illinois.gov/recreation/fishing/',
    regulations: 'https://www.dnr.illinois.gov/recreation/fishing/Pages/FishingRegulations.aspx',
  },
  IA: {
    name: 'Iowa',
    dnr: 'https://www.iowadnr.gov/Fishing/Ice-Fishing',
    fishing: 'https://www.iowadnr.gov/Fishing',
    regulations: 'https://www.iowadnr.gov/fishing/fishing-regulations',
  },
  ND: {
    name: 'North Dakota',
    dnr: 'https://gf.nd.gov/fishing/ice-fishing',
    fishing: 'https://gf.nd.gov/fishing',
    regulations: 'https://gf.nd.gov/gnf/fishing/regulations',
  },
  SD: {
    name: 'South Dakota',
    dnr: 'https://gfp.sd.gov/ice-fishing/',
    fishing: 'https://gfp.sd.gov/fishing/',
    regulations: 'https://gfp.sd.gov/fishing-regulations/',
  },
  MT: {
    name: 'Montana',
    dnr: 'https://fwp.mt.gov/fish/ice-fishing',
    fishing: 'https://fwp.mt.gov/fish',
    regulations: 'https://fwp.mt.gov/fish/regulations',
  },
  WY: {
    name: 'Wyoming',
    dnr: 'https://wgfd.wyo.gov/Fishing-and-Boating/Ice-Fishing',
    fishing: 'https://wgfd.wyo.gov/Fishing-and-Boating',
    regulations: 'https://wgfd.wyo.gov/Regulations/Regulation-PDFs/FISHREGS',
  },
  CO: {
    name: 'Colorado',
    dnr: 'https://cpw.state.co.us/learn/Pages/IceFishing.aspx',
    fishing: 'https://cpw.state.co.us/thingstodo/Pages/Fishing.aspx',
    regulations: 'https://cpw.state.co.us/learn/Pages/FishingRegulations.aspx',
  },
  ME: {
    name: 'Maine',
    dnr: 'https://www.maine.gov/ifw/fishing-boating/fishing/ice-fishing.html',
    fishing: 'https://www.maine.gov/ifw/fishing-boating/fishing/',
    regulations: 'https://www.maine.gov/ifw/fishing-boating/fishing/laws-rules/',
  },
  NH: {
    name: 'New Hampshire',
    dnr: 'https://www.wildlife.nh.gov/fishing/ice-fishing',
    fishing: 'https://www.wildlife.nh.gov/fishing',
    regulations: 'https://www.wildlife.nh.gov/fishing/fishing-regulations',
  },
  VT: {
    name: 'Vermont',
    dnr: 'https://vtfishandwildlife.com/fish/ice-fishing',
    fishing: 'https://vtfishandwildlife.com/fish',
    regulations: 'https://vtfishandwildlife.com/fish/fishing-regulations',
  },
  AK: {
    name: 'Alaska',
    dnr: 'https://www.adfg.alaska.gov/index.cfm?adfg=icefishing.main',
    fishing: 'https://www.adfg.alaska.gov/index.cfm?adfg=fishingSport.main',
    regulations: 'https://www.adfg.alaska.gov/index.cfm?adfg=fishregulations.main',
  },
};

// State abbreviation mapping from region strings
const stateAbbreviations = {
  'minnesota': 'MN', 'mn': 'MN',
  'wisconsin': 'WI', 'wi': 'WI',
  'michigan': 'MI', 'mi': 'MI',
  'new york': 'NY', 'ny': 'NY',
  'pennsylvania': 'PA', 'pa': 'PA',
  'ohio': 'OH', 'oh': 'OH',
  'indiana': 'IN', 'in': 'IN',
  'illinois': 'IL', 'il': 'IL',
  'iowa': 'IA', 'ia': 'IA',
  'north dakota': 'ND', 'nd': 'ND',
  'south dakota': 'SD', 'sd': 'SD',
  'montana': 'MT', 'mt': 'MT',
  'wyoming': 'WY', 'wy': 'WY',
  'colorado': 'CO', 'co': 'CO',
  'maine': 'ME', 'me': 'ME',
  'new hampshire': 'NH', 'nh': 'NH',
  'vermont': 'VT', 'vt': 'VT',
  'alaska': 'AK', 'ak': 'AK',
};

// Extract user's states from their favorite lakes
const userStates = computed(() => {
  const states = new Set();

  for (const lake of props.favoriteLakes) {
    if (!lake.region) continue;
    const regionLower = lake.region.toLowerCase();

    // Try to find state in the region string
    for (const [key, abbr] of Object.entries(stateAbbreviations)) {
      if (regionLower.includes(key)) {
        if (stateResources[abbr]) {
          states.add(abbr);
        }
        break;
      }
    }
  }

  return Array.from(states);
});

// Get resources for user's states
const userStateResources = computed(() => {
  return userStates.value.map(abbr => ({
    abbr,
    ...stateResources[abbr],
  }));
});

// Ice thickness safety data
const thicknessGuide = [
  { inches: '< 4"', activity: 'Stay off', color: 'bg-red-500', textColor: 'text-white', warning: true },
  { inches: '4"', activity: 'Ice fishing (walk)', color: 'bg-amber-400', textColor: 'text-amber-900', warning: false },
  { inches: '5-6"', activity: 'Snowmobile / ATV', color: 'bg-yellow-300', textColor: 'text-yellow-900', warning: false },
  { inches: '8-12"', activity: 'Small car', color: 'bg-sky-400', textColor: 'text-sky-900', warning: false },
  { inches: '12-15"', activity: 'Pickup truck', color: 'bg-emerald-400', textColor: 'text-emerald-900', warning: false },
];

function formatDate(dateStr) {
  if (!dateStr) return 'No date set';
  const d = new Date(dateStr);
  if (Number.isNaN(d.getTime())) return dateStr;
  return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
}

function formatRelativeTime(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  if (Number.isNaN(d.getTime())) return dateStr;

  const now = new Date();
  const diffMs = now - d;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  if (diffDays < 7) return `${diffDays}d ago`;
  return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
}

function getThicknessColor(inches) {
  if (inches == null) return 'bg-slate-100 text-slate-600';
  if (inches >= 12) return 'bg-emerald-100 text-emerald-700';
  if (inches >= 8) return 'bg-sky-100 text-sky-700';
  if (inches >= 4) return 'bg-amber-100 text-amber-700';
  return 'bg-red-100 text-red-700';
}

function baseLakeUrl(lake) {
  if (typeof window === 'undefined') return '';
  return `${window.location.origin}${route('lakes.show', lake.slug)}`;
}

function shareToX(lake) {
  const url = encodeURIComponent(baseLakeUrl(lake));
  const text = encodeURIComponent(`Checking ice reports for ${lake.name} on Safe Ice Tool`);
  window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank', 'noopener,noreferrer');
}

function shareToFacebook(lake) {
  const url = encodeURIComponent(baseLakeUrl(lake));
  window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'noopener,noreferrer');
}

async function copyLakeLink(lake) {
  const url = baseLakeUrl(lake);
  if (!url) return;

  try {
    await navigator.clipboard.writeText(url);
    copiedLakeId.value = lake.id;
    setTimeout(() => { copiedLakeId.value = null; }, 2000);
  } catch (e) {
    console.warn('Copy failed', e);
  }
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Dashboard" />

    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-slate-800">
        Welcome back, {{ user.name }}
      </h2>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

        <!-- Quick Actions -->
        <div class="mb-6 flex flex-wrap gap-3">
          <Link
            :href="route('lakes.index')"
            class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-sky-500 active:bg-sky-700 transition"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            Find Lakes
          </Link>
          <Link
            :href="route('trips.create')"
            class="inline-flex items-center gap-2 rounded-lg bg-slate-700 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-slate-600 active:bg-slate-800 transition"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Plan Trip
          </Link>
          <Link
            :href="route('lakes.create')"
            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 active:bg-slate-100 transition"
          >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Add Lake
          </Link>
        </div>

        <!-- Main Grid -->
        <div class="grid gap-6 lg:grid-cols-2">

          <!-- Favorite Lakes -->
          <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 sm:px-5">
              <h3 class="font-semibold text-slate-900">Favorite Lakes</h3>
              <Link :href="route('lakes.index')" class="text-sm text-sky-600 hover:text-sky-500">
                View all
              </Link>
            </div>

            <div v-if="favoriteLakes.length === 0" class="p-6 text-center">
              <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
              </div>
              <p class="text-sm text-slate-500 mb-3">No favorite lakes yet</p>
              <Link
                :href="route('lakes.index')"
                class="inline-flex items-center text-sm font-medium text-sky-600 hover:text-sky-500"
              >
                Browse lakes
                <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
              </Link>
            </div>

            <ul v-else class="divide-y divide-slate-100">
              <li
                v-for="lake in favoriteLakes"
                :key="lake.id"
                class="flex items-center justify-between gap-3 px-4 py-3 sm:px-5 hover:bg-slate-50 transition"
              >
                <Link :href="route('lakes.show', lake.slug)" class="min-w-0 flex-1">
                  <p class="truncate font-medium text-slate-900 hover:text-sky-600">{{ lake.name }}</p>
                  <p v-if="lake.region" class="truncate text-sm text-slate-500">{{ lake.region }}</p>
                </Link>

                <div class="flex items-center gap-1">
                  <button
                    type="button"
                    @click="shareToX(lake)"
                    class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
                    title="Share to X"
                  >
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                    </svg>
                  </button>
                  <button
                    type="button"
                    @click="shareToFacebook(lake)"
                    class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition"
                    title="Share to Facebook"
                  >
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                  </button>
                  <button
                    type="button"
                    @click="copyLakeLink(lake)"
                    class="rounded-lg p-2 transition"
                    :class="copiedLakeId === lake.id ? 'text-emerald-600 bg-emerald-50' : 'text-slate-400 hover:bg-slate-100 hover:text-slate-600'"
                    :title="copiedLakeId === lake.id ? 'Copied!' : 'Copy link'"
                  >
                    <svg v-if="copiedLakeId === lake.id" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                  </button>
                </div>
              </li>
            </ul>
          </div>

          <!-- Upcoming Trips -->
          <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 sm:px-5">
              <h3 class="font-semibold text-slate-900">Upcoming Trips</h3>
              <Link :href="route('trips.index')" class="text-sm text-sky-600 hover:text-sky-500">
                View all
              </Link>
            </div>

            <div v-if="upcomingTrips.length === 0" class="p-6 text-center">
              <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <p class="text-sm text-slate-500 mb-3">No trips planned</p>
              <Link
                :href="route('trips.create')"
                class="inline-flex items-center text-sm font-medium text-sky-600 hover:text-sky-500"
              >
                Plan a trip
                <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
              </Link>
            </div>

            <ul v-else class="divide-y divide-slate-100">
              <li
                v-for="trip in upcomingTrips"
                :key="trip.id"
                class="px-4 py-3 sm:px-5 hover:bg-slate-50 transition"
              >
                <div class="flex items-start justify-between gap-3">
                  <div class="min-w-0 flex-1">
                    <Link
                      v-if="trip.lake"
                      :href="route('lakes.show', trip.lake.slug)"
                      class="font-medium text-slate-900 hover:text-sky-600"
                    >
                      {{ trip.lake.name }}
                    </Link>
                    <span v-else class="font-medium text-slate-400">Lake unavailable</span>
                    <p v-if="trip.lake?.region" class="text-sm text-slate-500">{{ trip.lake.region }}</p>
                  </div>
                  <div class="flex-shrink-0 text-right">
                    <p class="text-sm font-medium text-slate-900">{{ formatDate(trip.trip_date) }}</p>
                    <p v-if="trip.time_of_day" class="text-xs text-slate-500">{{ trip.time_of_day }}</p>
                  </div>
                </div>

                <div v-if="trip.min_thickness_inches || trip.target_species" class="mt-2 flex flex-wrap gap-2">
                  <span v-if="trip.min_thickness_inches" class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-0.5 text-xs font-medium text-sky-700">
                    Min {{ trip.min_thickness_inches }}"
                  </span>
                  <span v-if="trip.target_species" class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                    {{ trip.target_species }}
                  </span>
                  <span v-if="trip.avoid_slush" class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                    No slush
                  </span>
                  <span v-if="trip.avoid_pressure_cracks" class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                    No cracks
                  </span>
                </div>
              </li>
            </ul>
          </div>

          <!-- Recent Reports - Full Width -->
          <div class="lg:col-span-2 rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3 sm:px-5">
              <h3 class="font-semibold text-slate-900">Recent Reports</h3>
              <Link :href="route('lakes.index')" class="text-sm text-sky-600 hover:text-sky-500">
                Explore lakes
              </Link>
            </div>

            <div v-if="recentReports.length === 0" class="p-6 text-center">
              <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                <svg class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <p class="text-sm text-slate-500">No recent reports on your favorite lakes</p>
            </div>

            <div v-else class="grid gap-px bg-slate-100 sm:grid-cols-2 lg:grid-cols-3">
              <div
                v-for="r in recentReports"
                :key="r.id"
                class="bg-white p-4 hover:bg-slate-50 transition"
              >
                <div class="flex items-start justify-between gap-2 mb-2">
                  <Link
                    v-if="r.lake"
                    :href="route('lakes.show', r.lake.slug)"
                    class="font-medium text-slate-900 hover:text-sky-600 truncate"
                  >
                    {{ r.lake.name }}
                  </Link>
                  <span v-else class="font-medium text-slate-400">Lake unavailable</span>
                  <span class="flex-shrink-0 text-xs text-slate-500">{{ formatRelativeTime(r.created_at) }}</span>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                  <span
                    v-if="r.thickness_inches != null"
                    :class="getThicknessColor(r.thickness_inches)"
                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                  >
                    {{ r.thickness_inches }}"
                  </span>
                  <span v-if="r.ice_type" class="text-xs text-slate-600">{{ r.ice_type }}</span>
                  <span v-if="r.has_slush" class="inline-flex items-center gap-1 text-xs text-amber-600">
                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Slush
                  </span>
                  <span v-if="r.has_pressure_cracks" class="inline-flex items-center gap-1 text-xs text-amber-600">
                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Cracks
                  </span>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Safety & Resources Section -->
        <div class="mt-8">
          <h3 class="text-lg font-semibold text-slate-900 mb-4">Safety & Resources</h3>

          <div class="grid gap-6 lg:grid-cols-3">

            <!-- Ice Thickness Safety Guide -->
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
              <div class="border-b border-slate-100 px-4 py-3 sm:px-5">
                <div class="flex items-center gap-2">
                  <svg class="h-5 w-5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                  </svg>
                  <h4 class="font-semibold text-slate-900">Ice Thickness Guide</h4>
                </div>
              </div>
              <div class="p-4">
                <div class="space-y-2">
                  <div
                    v-for="item in thicknessGuide"
                    :key="item.inches"
                    class="flex items-center gap-3"
                  >
                    <span
                      :class="[item.color, item.textColor]"
                      class="inline-flex items-center justify-center w-14 rounded-md px-2 py-1 text-xs font-bold"
                    >
                      {{ item.inches }}
                    </span>
                    <span class="text-sm text-slate-700 flex-1">{{ item.activity }}</span>
                    <svg v-if="item.warning" class="h-4 w-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </div>
                <p class="mt-4 text-xs text-slate-500">
                  These are general guidelines for clear, solid ice. White/snow ice is only half as strong. Always check conditions locally.
                </p>
              </div>
            </div>

            <!-- State DNR Resources (if user has favorite lakes) -->
            <div v-if="userStateResources.length > 0" class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
              <div class="border-b border-slate-100 px-4 py-3 sm:px-5">
                <div class="flex items-center gap-2">
                  <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                  <h4 class="font-semibold text-slate-900">Your State DNR</h4>
                </div>
              </div>
              <div class="p-4">
                <div v-for="state in userStateResources" :key="state.abbr" class="mb-4 last:mb-0">
                  <h5 class="text-sm font-medium text-slate-900 mb-2">{{ state.name }}</h5>
                  <div class="flex flex-wrap gap-2">
                    <a
                      :href="state.dnr"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="inline-flex items-center gap-1 rounded-lg bg-sky-50 px-3 py-1.5 text-xs font-medium text-sky-700 hover:bg-sky-100 transition"
                    >
                      <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                      </svg>
                      Ice Safety
                    </a>
                    <a
                      :href="state.fishing"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:bg-emerald-100 transition"
                    >
                      <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064" />
                      </svg>
                      Fishing Info
                    </a>
                    <a
                      :href="state.regulations"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="inline-flex items-center gap-1 rounded-lg bg-amber-50 px-3 py-1.5 text-xs font-medium text-amber-700 hover:bg-amber-100 transition"
                    >
                      <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                      </svg>
                      Regulations
                    </a>
                  </div>
                </div>
              </div>
            </div>

            <!-- General Resources (shown when no state-specific resources) -->
            <div v-else class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
              <div class="border-b border-slate-100 px-4 py-3 sm:px-5">
                <div class="flex items-center gap-2">
                  <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                  </svg>
                  <h4 class="font-semibold text-slate-900">Helpful Resources</h4>
                </div>
              </div>
              <div class="p-4">
                <p class="text-sm text-slate-500 mb-3">
                  Add favorite lakes to see DNR resources for your state.
                </p>
                <div class="space-y-2">
                  <a
                    href="https://www.weather.gov"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center gap-2 text-sm text-sky-600 hover:text-sky-500"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                    </svg>
                    National Weather Service
                  </a>
                  <a
                    href="https://www.redcross.org/get-help/how-to-prepare-for-emergencies/types-of-emergencies/winter-storm/frozen-lake-ice-safety.html"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center gap-2 text-sm text-sky-600 hover:text-sky-500"
                  >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    Red Cross Ice Safety
                  </a>
                </div>
              </div>
            </div>

            <!-- Weather & Conditions -->
            <div class="rounded-xl bg-white shadow-sm ring-1 ring-slate-200 overflow-hidden">
              <div class="border-b border-slate-100 px-4 py-3 sm:px-5">
                <div class="flex items-center gap-2">
                  <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                  </svg>
                  <h4 class="font-semibold text-slate-900">Weather & Conditions</h4>
                </div>
              </div>
              <div class="p-4 space-y-3">
                <a
                  href="https://www.weather.gov"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="flex items-center justify-between p-3 rounded-lg bg-slate-50 hover:bg-slate-100 transition group"
                >
                  <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
                      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-slate-900">Weather.gov</p>
                      <p class="text-xs text-slate-500">Official NWS forecasts</p>
                    </div>
                  </div>
                  <svg class="h-4 w-4 text-slate-400 group-hover:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                  </svg>
                </a>
                <a
                  href="https://www.wunderground.com"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="flex items-center justify-between p-3 rounded-lg bg-slate-50 hover:bg-slate-100 transition group"
                >
                  <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-slate-900">Weather Underground</p>
                      <p class="text-xs text-slate-500">Local station data</p>
                    </div>
                  </div>
                  <svg class="h-4 w-4 text-slate-400 group-hover:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                  </svg>
                </a>
              </div>
            </div>

          </div>
        </div>

        <!-- Emergency & Safety Tips -->
        <div class="mt-6 rounded-xl bg-gradient-to-r from-slate-800 to-slate-900 shadow-lg overflow-hidden">
          <div class="p-5 sm:p-6">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0 flex h-12 w-12 items-center justify-center rounded-xl bg-red-500/20 text-red-400">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div class="flex-1">
                <h4 class="text-lg font-semibold text-white mb-2">If You Fall Through Ice</h4>
                <ol class="text-sm text-slate-300 space-y-1.5 list-decimal list-inside">
                  <li><span class="text-white font-medium">Stay calm</span> - control your breathing, you have minutes to act</li>
                  <li><span class="text-white font-medium">Don't remove clothing</span> - it traps air and helps you float</li>
                  <li><span class="text-white font-medium">Turn toward where you came from</span> - that ice held your weight</li>
                  <li><span class="text-white font-medium">Kick and pull yourself out</span> - use ice picks if you have them</li>
                  <li><span class="text-white font-medium">Roll away from the hole</span> - don't stand until you're on solid ice</li>
                  <li><span class="text-white font-medium">Get warm immediately</span> - remove wet clothes, call 911</li>
                </ol>
              </div>
            </div>
          </div>
          <div class="bg-slate-900/50 px-5 py-3 sm:px-6 border-t border-slate-700">
            <div class="flex flex-wrap items-center gap-4 text-xs">
              <span class="text-slate-400">Always carry:</span>
              <span class="inline-flex items-center gap-1.5 text-slate-300">
                <svg class="h-4 w-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                Ice picks
              </span>
              <span class="inline-flex items-center gap-1.5 text-slate-300">
                <svg class="h-4 w-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                Rope
              </span>
              <span class="inline-flex items-center gap-1.5 text-slate-300">
                <svg class="h-4 w-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                Charged phone
              </span>
              <span class="inline-flex items-center gap-1.5 text-slate-300">
                <svg class="h-4 w-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                Whistle
              </span>
              <span class="inline-flex items-center gap-1.5 text-slate-300">
                <svg class="h-4 w-4 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                Dry clothes in vehicle
              </span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>
