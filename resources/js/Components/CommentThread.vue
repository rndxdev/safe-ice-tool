<script setup>
import { computed } from 'vue';

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
  showHeader: {
    type: Boolean,
    default: true,
  },
  title: {
    type: String,
    default: 'Comments',
  },
});

const emit = defineEmits(['like']);

function displayName(user) {
  if (!user) return 'Anonymous';
  return user.username || user.name || 'Anonymous';
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
      <li v-for="comment in comments" :key="comment.id" class="px-4 py-4">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="text-sm font-medium text-slate-900">
              {{ displayName(comment.user) }}
            </p>
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

        <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">
          {{ comment.body }}
        </p>

        <div v-if="comment.replies && comment.replies.length" class="mt-3 space-y-3 border-l border-slate-200 pl-4">
          <div v-for="reply in comment.replies" :key="reply.id" class="pt-1">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="text-sm font-medium text-slate-900">
                  {{ displayName(reply.user) }}
                </p>
                <p class="text-xs text-slate-500">
                  {{ formatTimestamp(reply.created_at) }}
                </p>
              </div>
              <button
                v-if="showLikes"
                type="button"
                @click="$emit('like', reply)"
                :class="reply.liked ? 'text-emerald-600' : 'text-slate-500'"
                :disabled="!canLike"
                class="inline-flex items-center gap-1 text-xs hover:text-emerald-600 transition disabled:cursor-not-allowed disabled:opacity-60"
              >
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                  <path
                    d="M2 10c0-1.105.895-2 2-2h3.28l.7-3.03A2 2 0 0110.93 3h.07a2 2 0 011.94 1.515L13.6 8H16a2 2 0 012 2v1a2 2 0 01-.276 1.01l-2.21 3.68A2 2 0 0113.8 16H8a2 2 0 01-2-2v-1H4a2 2 0 01-2-2v-1z"
                  />
                </svg>
                Like
                <span class="text-slate-400">({{ reply.like_count || 0 }})</span>
              </button>
            </div>

            <p class="mt-2 text-sm text-slate-700 whitespace-pre-line">
              {{ reply.body }}
            </p>
          </div>
        </div>
      </li>
    </ul>
  </div>
</template>
