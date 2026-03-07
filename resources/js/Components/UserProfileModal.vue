<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  username: {
    type: String,
    default: null,
  },
  show: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['close']);

const loading = ref(false);
const error = ref(null);
const profile = ref(null);

watch(() => props.username, async (newUsername) => {
  if (!newUsername) {
    profile.value = null;
    return;
  }

  loading.value = true;
  error.value = null;

  try {
    const response = await fetch(`/users/${newUsername}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    if (!response.ok) {
      throw new Error('User not found');
    }

    profile.value = await response.json();
  } catch (e) {
    error.value = e.message || 'Failed to load profile';
    profile.value = null;
  } finally {
    loading.value = false;
  }
}, { immediate: true });

function close() {
  emit('close');
}

function handleBackdropClick(e) {
  if (e.target === e.currentTarget) {
    close();
  }
}
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
        @click="handleBackdropClick"
      >
        <Transition
          enter-active-class="transition ease-out duration-200"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition ease-in duration-150"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div
            v-if="show"
            class="relative w-full max-w-sm bg-white rounded-xl shadow-xl overflow-hidden"
            @click.stop
          >
            <!-- Close button -->
            <button
              type="button"
              @click="close"
              class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 transition z-10"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>

            <!-- Loading state -->
            <div v-if="loading" class="p-8 text-center">
              <div class="animate-spin h-8 w-8 border-4 border-sky-500 border-t-transparent rounded-full mx-auto"></div>
              <p class="mt-3 text-sm text-gray-500">Loading profile...</p>
            </div>

            <!-- Error state -->
            <div v-else-if="error" class="p-8 text-center">
              <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <p class="text-sm text-gray-600">{{ error }}</p>
            </div>

            <!-- Profile content -->
            <div v-else-if="profile" class="p-6">
              <!-- Header -->
              <div class="text-center mb-4">
                <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-sky-400 to-sky-600 text-white text-2xl font-bold">
                  {{ (profile.username || profile.name || '?')[0].toUpperCase() }}
                </div>
                <h3 class="text-lg font-semibold text-gray-900">
                  <span v-if="profile.username" class="text-sky-600">@{{ profile.username }}</span>
                  <span v-else>{{ profile.name || 'Anonymous' }}</span>
                </h3>
                <p v-if="profile.name && profile.username" class="text-sm text-gray-500">{{ profile.name }}</p>
              </div>

              <!-- Bio -->
              <div v-if="profile.bio" class="mb-4">
                <p class="text-sm text-gray-700 text-center whitespace-pre-line">{{ profile.bio }}</p>
              </div>

              <!-- Info -->
              <div class="space-y-2 mb-4">
                <div v-if="profile.location" class="flex items-center justify-center gap-2 text-sm text-gray-600">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  {{ profile.location }}
                </div>
                <div v-if="profile.member_since" class="flex items-center justify-center gap-2 text-sm text-gray-500">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  Member since {{ profile.member_since }}
                </div>
              </div>

              <!-- Stats -->
              <div v-if="profile.stats" class="grid grid-cols-2 gap-3 pt-4 border-t border-gray-100">
                <div class="text-center">
                  <p class="text-2xl font-bold text-gray-900">{{ profile.stats.reports_count }}</p>
                  <p class="text-xs text-gray-500">Ice Reports</p>
                </div>
                <div class="text-center">
                  <p class="text-2xl font-bold text-gray-900">{{ profile.stats.lakes_count }}</p>
                  <p class="text-xs text-gray-500">Favorite Lakes</p>
                </div>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
