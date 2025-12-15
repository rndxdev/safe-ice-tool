<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
  trip: { type: Object, required: true },
});

const form = useForm({
  caption: '',
  media: [],
});

function onFiles(e) {
  const files = Array.from(e.target.files || []);
  form.media = files;
}

function submit() {
  form.post(route('trips.post.store', props.trip.id), {
    forceFormData: true,
    preserveScroll: true,
  });
}
</script>

<template>
  <AuthenticatedLayout>
    <Head title="Create post" />

    <div class="max-w-3xl mx-auto p-4 space-y-4">
      <div class="flex justify-between items-center">
        <Link :href="route('trips.index')" class="text-sm text-sky-600 hover:underline">
          Back to trips
        </Link>
      </div>

      <div class="border rounded-lg bg-white p-4 space-y-4">
        <div>
          <div class="text-sm text-gray-700 mb-1">Caption</div>
          <textarea
            v-model="form.caption"
            rows="4"
            class="w-full border rounded px-2 py-2 text-sm"
            placeholder="Add your notes, fish pics, ice conditions, etc."
          ></textarea>
          <div v-if="form.errors.caption" class="text-sm text-red-600 mt-1">
            {{ form.errors.caption }}
          </div>
        </div>

        <div>
          <div class="text-sm text-gray-700 mb-1">Photos</div>
          <input
            type="file"
            multiple
            accept="image/*"
            class="block w-full text-sm"
            @change="onFiles"
          />
          <div v-if="form.errors.media" class="text-sm text-red-600 mt-1">
            {{ form.errors.media }}
          </div>
          <div v-if="form.errors['media.0']" class="text-sm text-red-600 mt-1">
            {{ form.errors['media.0'] }}
          </div>
        </div>

        <div class="flex justify-end">
          <button
            type="button"
            class="px-4 py-2 rounded bg-sky-500 hover:bg-sky-400 text-sm text-white"
            :disabled="form.processing"
            @click="submit"
          >
            Create post
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
