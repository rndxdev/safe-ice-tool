<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import UserHandle from '@/Components/UserHandle.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
  post: { type: Object, required: true },
  comments: { type: Array, default: () => [] },
  shareUrl: { type: String, default: '' },
});

const form = useForm({
  body: '',
});

function submitComment() {
  form.post(route('posts.comments.store', props.post.id), {
    preserveScroll: true,
    onSuccess: () => form.reset('body'),
  });
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Post" />

    <div class="max-w-3xl mx-auto p-4 space-y-4">
      <div class="flex justify-between items-center">
        <Link :href="route('posts.index')" class="text-sm text-sky-600 hover:underline">
          Back to community
        </Link>
      </div>

      <div class="border rounded-lg bg-white p-4">
        <div class="text-sm text-gray-900 whitespace-pre-line">
          {{ post.caption }}
        </div>
      </div>

      <div class="border rounded-lg bg-white p-4 space-y-3">
        <div class="text-lg font-medium">
          Comments ({{ comments.length }})
        </div>

        <div v-if="comments.length === 0" class="text-sm text-gray-500">
          No comments yet.
        </div>

        <ul v-else class="space-y-2">
          <li v-for="c in comments" :key="c.id" class="border rounded p-3">
            <div class="text-xs text-gray-500">
              <UserHandle :user="c.user" fallback="User" class="text-xs" />
              <span v-if="c.created_at"> · {{ new Date(c.created_at).toLocaleString() }}</span>
            </div>
            <div class="text-sm text-gray-900 whitespace-pre-line mt-1">
              {{ c.body }}
            </div>
          </li>
        </ul>

        <form class="space-y-2" @submit.prevent="submitComment">
          <textarea
            v-model="form.body"
            rows="3"
            class="w-full border rounded px-2 py-2 text-sm"
            placeholder="Add a comment..."
          ></textarea>

          <div v-if="form.errors.body" class="text-sm text-red-600">
            {{ form.errors.body }}
          </div>

          <div class="flex justify-end">
            <button
              type="submit"
              class="px-4 py-2 rounded bg-sky-500 hover:bg-sky-400 text-sm text-white"
              :disabled="form.processing"
            >
              Post comment
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
