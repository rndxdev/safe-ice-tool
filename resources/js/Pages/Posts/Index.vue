<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CommentThread from '@/Components/CommentThread.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
  posts: {
    type: Array,
    required: true,
  },
});

function formatDateTime(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  if (Number.isNaN(d.getTime())) return String(dateStr);
  return d.toLocaleString();
}

function likePostComment(comment) {
  router.post(route('comments.like'), {
    comment_type: 'trip_post_comment',
    comment_id: comment.id,
  }, { preserveScroll: true });
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Community" />

    <div class="max-w-4xl mx-auto p-4 space-y-4">
      <header class="space-y-1">
        <h1 class="text-2xl font-semibold">
          Community
        </h1>
        <p class="text-sm text-gray-500">
          Shared trip recaps from the community. Use them for ideas, not guarantees.
        </p>
      </header>

      <div v-if="posts.length === 0" class="text-sm text-gray-500">
        No posts yet. Create a trip post from your Trips page.
      </div>

      <ul v-else class="space-y-3">
        <li
          v-for="p in posts"
          :key="p.id"
          class="border rounded-lg bg-white p-4"
        >
          <div class="flex justify-between items-start gap-3">
            <div class="min-w-0">
              <div class="text-sm text-gray-500">
                <span v-if="p.trip?.lake?.slug">
                  <Link
                    :href="route('lakes.show', p.trip.lake.slug)"
                    class="text-sky-600 hover:underline"
                  >
                    {{ p.trip.lake.name }}
                  </Link>
                  <span v-if="p.trip.lake.region"> · {{ p.trip.lake.region }}</span>
                </span>
                <span v-else>
                  Trip recap
                </span>
              </div>

              <div class="text-xs text-gray-400 mt-1">
                {{ formatDateTime(p.created_at) }}
              </div>
            </div>

            <Link
              :href="route('posts.show', p.id)"
              class="text-xs text-sky-600 hover:underline shrink-0"
            >
              View
            </Link>
          </div>

          <div v-if="p.caption" class="mt-3 text-sm text-gray-900 whitespace-pre-line">
            {{ p.caption }}
          </div>

          <div v-if="p.media_count" class="mt-3 text-xs text-gray-500">
            Media: {{ p.media_count }}
          </div>

          <div v-if="p.comments_count" class="mt-1 text-xs text-gray-500">
            Comments: {{ p.comments_count }}
          </div>

          <div v-if="p.comments_preview && p.comments_preview.length" class="mt-3">
            <CommentThread
              :comments="p.comments_preview"
              :showLikes="true"
              :canLike="true"
              :showHeader="false"
              @like="likePostComment"
            />
          </div>
        </li>
      </ul>
    </div>
  </AuthenticatedLayout>
</template>
