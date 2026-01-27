<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  lakes: {
    type: Array,
    required: true,
  },
  reviewLakes: {
    type: Array,
    required: true,
  },
});

function statusBadgeClass(status) {
  if (status === 'approved') {
    return 'text-green-700 bg-green-50 border border-green-200 px-2 py-0.5 rounded text-[11px]';
  }
  if (status === 'rejected') {
    return 'text-red-700 bg-red-50 border border-red-200 px-2 py-0.5 rounded text-[11px]';
  }
  return 'text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded text-[11px]';
}

function statusLabel(status) {
  if (status === 'approved') {
    return 'Approved';
  }
  if (status === 'rejected') {
    return 'Rejected';
  }
  return 'Pending review';
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

function verdictLabel(verdict) {
  if (verdict === 'approve') return 'Approved';
  if (verdict === 'reject') return 'Rejected';
  if (verdict === 'flag') return 'Flagged';
  return 'Not voted';
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="My lakes" />

    <div class="max-w-4xl mx-auto p-4 space-y-4">
      <header class="space-y-2">
        <h1 class="text-2xl font-semibold">
          My lakes
        </h1>

        <p class="text-sm text-gray-500">
          Lakes you’ve added to Safe Ice Tool. Pending lakes are only visible to you until they’re approved.
        </p>

        <p class="text-sm text-gray-600">
          How verification works: new lakes start as pending to keep the public directory clean. A lake is typically approved once it has a real ice report and the location looks accurate (and not flagged by other users). If a pin is clearly wrong or spammy, it may be rejected.
        </p>
      </header>

      <div class="flex justify-end">
        <Link
          :href="route('lakes.create')"
          class="px-3 py-2 rounded-md bg-sky-500 hover:bg-sky-400 text-sm text-white"
        >
          Add a lake
        </Link>
      </div>

      <div v-if="lakes.length === 0" class="text-sm text-gray-500">
        You haven’t added any lakes yet. Add your first lake by dropping a pin.
      </div>

      <ul v-else class="space-y-2">
        <li
          v-for="lake in lakes"
          :key="lake.id"
          class="border rounded-lg p-3 bg-white flex items-start justify-between gap-4"
        >
          <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <div class="font-medium truncate">
                {{ lake.name }}
              </div>

              <span :class="statusBadgeClass(lake.status)">
                {{ statusLabel(lake.status) }}
              </span>
            </div>

            <div v-if="lake.region" class="text-xs text-gray-500 mt-1">
              {{ lake.region }}
            </div>

            <div class="text-xs text-gray-500 mt-1">
              <span v-if="lake.state">{{ lake.state }}</span>
              <span v-if="lake.state && lake.county"> · </span>
              <span v-if="lake.county">{{ lake.county }}</span>
            </div>

            <div v-if="lake.lat != null && lake.lng != null" class="text-xs text-gray-400 mt-1">
              {{ Number(lake.lat).toFixed(5) }}, {{ Number(lake.lng).toFixed(5) }}
            </div>

            <div v-if="lake.created_at" class="text-xs text-gray-400 mt-1">
              Submitted: {{ formatDateTime(lake.created_at) }}
            </div>
          </div>

          <div class="shrink-0 flex flex-col items-end gap-2">
            <Link
              :href="route('lakes.show', lake.slug)"
              class="text-xs text-sky-600 hover:underline"
            >
              View
            </Link>

            <Link
              v-if="lake.status === 'pending'"
              :href="route('lakes.show', lake.slug)"
              class="text-xs text-gray-500 hover:underline"
            >
              Add first report
            </Link>
          </div>
        </li>
      </ul>

      <div class="text-xs text-gray-500 border rounded-lg bg-white p-3">
        Note: “Pending review” helps keep the public lake directory clean and makes spam harder. If you accidentally pinned the wrong spot, add a corrected lake and ignore the old one for now.
      </div>

      <div class="border rounded-lg bg-white p-4 space-y-3">
        <div class="flex items-center justify-between gap-3">
          <div>
            <h2 class="text-lg font-semibold">Verify lakes</h2>
            <p class="text-sm text-gray-500">
              Help confirm locations added by other anglers. Approvals speed up reviews. Flags surface spam or bad pins.
            </p>
          </div>
        </div>

        <div v-if="reviewLakes.length === 0" class="text-sm text-gray-500">
          No pending lakes to verify right now.
        </div>

        <ul v-else class="space-y-2">
          <li
            v-for="lake in reviewLakes"
            :key="lake.id"
            class="border rounded-lg p-3 bg-slate-50 flex items-start justify-between gap-4"
          >
            <div class="min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <div class="font-medium truncate">
                  {{ lake.name }}
                </div>
                <span class="text-[11px] px-2 py-0.5 rounded border border-slate-200 bg-white text-slate-600">
                  Reports: {{ lake.report_count || 0 }}
                </span>
                <span class="text-[11px] px-2 py-0.5 rounded border border-emerald-200 bg-emerald-50 text-emerald-700">
                  Approvals: {{ lake.verification_counts?.approve || 0 }}
                </span>
                <span class="text-[11px] px-2 py-0.5 rounded border border-amber-200 bg-amber-50 text-amber-700">
                  Flags: {{ lake.verification_counts?.flag || 0 }}
                </span>
                <span class="text-[11px] px-2 py-0.5 rounded border border-red-200 bg-red-50 text-red-700">
                  Rejects: {{ lake.verification_counts?.reject || 0 }}
                </span>
              </div>

              <div v-if="lake.region" class="text-xs text-gray-500 mt-1">
                {{ lake.region }}
              </div>

              <div class="text-xs text-gray-500 mt-1">
                <span v-if="lake.state">{{ lake.state }}</span>
                <span v-if="lake.state && lake.county"> · </span>
                <span v-if="lake.county">{{ lake.county }}</span>
              </div>

              <div v-if="lake.lat != null && lake.lng != null" class="text-xs text-gray-400 mt-1">
                {{ Number(lake.lat).toFixed(5) }}, {{ Number(lake.lng).toFixed(5) }}
              </div>

              <div v-if="lake.created_at" class="text-xs text-gray-400 mt-1">
                Submitted: {{ formatDateTime(lake.created_at) }}
              </div>
            </div>

            <div class="shrink-0 flex flex-col items-end gap-2">
              <Link
                :href="route('lakes.show', lake.slug)"
                class="text-xs text-sky-600 hover:underline"
              >
                View
              </Link>

              <div class="text-[11px] text-gray-500">
                Your vote: {{ verdictLabel(lake.user_verdict) }}
              </div>

              <div class="flex flex-wrap gap-2">
                <Link
                  :href="route('lakes.verify', lake.id)"
                  method="post"
                  as="button"
                  :data="{ verdict: 'approve' }"
                  class="px-2.5 py-1 rounded-md bg-emerald-600 hover:bg-emerald-500 text-[11px] text-white"
                >
                  Approve
                </Link>
                <Link
                  :href="route('lakes.verify', lake.id)"
                  method="post"
                  as="button"
                  :data="{ verdict: 'flag' }"
                  class="px-2.5 py-1 rounded-md bg-amber-500 hover:bg-amber-400 text-[11px] text-white"
                >
                  Flag
                </Link>
                <Link
                  :href="route('lakes.verify', lake.id)"
                  method="post"
                  as="button"
                  :data="{ verdict: 'reject' }"
                  class="px-2.5 py-1 rounded-md bg-red-600 hover:bg-red-500 text-[11px] text-white"
                >
                  Reject
                </Link>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
