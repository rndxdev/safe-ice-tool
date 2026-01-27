<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import CommentThread from '@/Components/CommentThread.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  post: {
    type: Object,
    required: true,
  },
  comments: {
    type: Array,
    required: true,
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

function formatDateTime(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  if (Number.isNaN(d.getTime())) return String(dateStr);
  return d.toLocaleString();
}

async function copyLink() {
  const url = props.shareUrl;

  if (navigator.clipboard?.writeText) {
    try {
      await navigator.clipboard.writeText(url);
      alert('Link copied.');
      return;
    } catch {
      prompt('Copy this link:', url);
      return;
    }
  }

  prompt('Copy this link:', url);
}

function shareToX() {
  const url = props.shareUrl;
  const lakeName = props.post?.trip?.lake?.name ?? 'a lake';
  const text = `Trip recap: ${lakeName}. ${url}`;
  window.open(`https://x.com/intent/tweet?text=${encodeURIComponent(text)}`, '_blank', 'noopener,noreferrer');
}

function shareNative() {
  const url = props.shareUrl;

  if (!navigator.share) {
    copyLink();
    return;
  }

  navigator.share({
    title: 'Safe Ice Tool trip recap',
    text: 'Shared trip recap',
    url,
  }).catch(() => {});
}
</script>

<template>
  <GuestLayout>
    <Head title="Shared post" />

    <div class="w-full sm:max-w-2xl space-y-4">
      <div class="bg-white border rounded-lg shadow-sm p-5 space-y-3">
        <div class="text-xs text-gray-500">
          Shared trip recap
        </div>

        <div class="text-lg font-semibold text-gray-900">
          <span v-if="post.trip?.lake?.name">{{ post.trip.lake.name }}</span>
          <span v-else>Trip recap</span>
        </div>

        <div v-if="post.trip?.lake?.region" class="text-sm text-gray-500">
          {{ post.trip.lake.region }}
        </div>

        <div class="text-xs text-gray-400">
          {{ formatDateTime(post.created_at) }}
        </div>

        <div v-if="post.caption" class="text-sm text-gray-900 whitespace-pre-line pt-2">
          {{ post.caption }}
        </div>

        <div v-if="post.media && post.media.length" class="pt-2">
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
            <a
              v-for="m in post.media"
              :key="m.id"
              :href="m.url"
              target="_blank"
              rel="noopener noreferrer"
              class="border rounded overflow-hidden bg-gray-50"
            >
              <img :src="m.url" alt="" class="w-full h-32 object-cover" />
            </a>
          </div>
        </div>

        <div class="flex flex-wrap gap-2 pt-2">
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
            :href="route('welcome')"
            class="px-3 py-2 rounded-md border border-gray-300 hover:bg-gray-50 text-sm"
          >
            Learn more
          </Link>
        </div>
      </div>

      <div class="bg-white border rounded-lg shadow-sm p-5 space-y-3">
        <div class="text-lg font-medium">
          Comments
        </div>

        <div class="text-sm text-gray-500">
          Comments are visible here, but posting requires an account.
        </div>

        <CommentThread
          :comments="comments"
          :showLikes="true"
          :canLike="false"
          :showHeader="false"
        />

        <div class="flex gap-2 pt-2">
          <Link
            v-if="canLogin"
            :href="route('login')"
            class="px-3 py-2 rounded-md border border-gray-300 hover:bg-gray-50 text-sm"
          >
            Log in to comment
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
