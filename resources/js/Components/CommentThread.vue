<script setup>
import { computed, ref } from 'vue';
import UserHandle from './UserHandle.vue';

const props = defineProps({
  comments: {
    type: Array,
    default: () => [],
  },
  showLikes: {
    type: Boolean,
    default: true,
  },
  canLike: {
    type: Boolean,
    default: true,
  },
  canReply: {
    type: Boolean,
    default: true,
  },
  showHeader: {
    type: Boolean,
    default: true,
  },
  title: {
    type: String,
    default: 'Comments',
  },
  highlightedId: {
    type: [Number, String],
    default: null,
  },
});

const emit = defineEmits(['like', 'reply']);

const replyOpen = ref({});
const replyDrafts = ref({});

function toggleReply(commentId) {
  replyOpen.value[commentId] = !replyOpen.value[commentId];
}

function submitReply(comment) {
  const body = (replyDrafts.value[comment.id] || '').trim();
  if (!body) return;
  emit('reply', { comment, body });
  replyDrafts.value[comment.id] = '';
  replyOpen.value[comment.id] = false;
}

function formatTimestamp(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  if (Number.isNaN(d.getTime())) return dateStr;
  return d.toLocaleString(undefined, {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  });
}

const hasComments = computed(() => props.comments && props.comments.length > 0);
</script>

<template>
  <div class="rounded-xl border border-slate-200 bg-white">
    <div v-if="showHeader" class="border-b border-slate-100 px-4 py-3">
      <h4 class="text-sm font-semibold text-slate-900">{{ title }}</h4>
    </div>

    <div v-if="!hasComments" class="px-4 py-6 text-center text-sm text-slate-500">
      No comments yet.
    </div>

    <ul v-else class="divide-y divide-slate-100">
      <li
        v-for="comment in comments"
        :key="comment.id"
        :id="`comment-${comment.id}`"
        :class="[
          'px-4 py-3 transition-colors duration-500',
          highlightedId && String(highlightedId) === String(comment.id)
            ? 'bg-sky-50 ring-2 ring-sky-200 ring-inset'
            : ''
        ]"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
              <UserHandle :user="comment.user" class="text-sm font-medium" />
              <span v-if="comment.reply_to_user" class="text-xs text-slate-400">
                <svg class="inline h-3 w-3 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                </svg>
                <span class="text-sky-600">@{{ comment.reply_to_user }}</span>
              </span>
            </div>
            <p class="text-xs text-slate-500">
              {{ formatTimestamp(comment.created_at) }}
            </p>
          </div>
          <button
            v-if="showLikes"
            type="button"
            @click="$emit('like', comment)"
            :class="comment.liked ? 'text-emerald-600' : 'text-slate-500'"
            :disabled="!canLike"
            class="inline-flex items-center gap-1 text-xs hover:text-emerald-600 transition disabled:cursor-not-allowed disabled:opacity-60"
          >
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
              <path
                d="M2 10c0-1.105.895-2 2-2h3.28l.7-3.03A2 2 0 0110.93 3h.07a2 2 0 011.94 1.515L13.6 8H16a2 2 0 012 2v1a2 2 0 01-.276 1.01l-2.21 3.68A2 2 0 0113.8 16H8a2 2 0 01-2-2v-1H4a2 2 0 01-2-2v-1z"
              />
            </svg>
            Like
            <span class="text-slate-400">({{ comment.like_count || 0 }})</span>
          </button>
        </div>

        <p class="mt-1.5 text-sm text-slate-700 whitespace-pre-line">
          {{ comment.body }}
        </p>

        <div class="mt-2 flex items-center gap-3">
          <button
            v-if="canReply"
            type="button"
            @click="toggleReply(comment.id)"
            class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-slate-700 transition"
          >
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
            </svg>
            Reply
          </button>
        </div>

        <div v-if="replyOpen[comment.id]" class="mt-3">
          <div class="text-xs text-slate-500 mb-1.5">
            Replying to <UserHandle :user="comment.user" class="text-xs" />
          </div>
          <textarea
            v-model="replyDrafts[comment.id]"
            rows="2"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-sky-500 focus:ring-2 focus:ring-sky-200"
            placeholder="Write a reply..."
          ></textarea>
          <div class="mt-2 flex items-center gap-2">
            <button
              type="button"
              @click="submitReply(comment)"
              class="inline-flex items-center rounded-lg bg-sky-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-sky-500 transition"
            >
              Reply
            </button>
            <button
              type="button"
              @click="toggleReply(comment.id)"
              class="inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-800 transition"
            >
              Cancel
            </button>
          </div>
        </div>
      </li>
    </ul>
  </div>
</template>
