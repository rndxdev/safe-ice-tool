<script setup>
import { ref, computed } from 'vue';
import UserProfileModal from './UserProfileModal.vue';

const props = defineProps({
  user: {
    type: Object,
    default: null,
  },
  fallback: {
    type: String,
    default: 'Anonymous',
  },
  showAt: {
    type: Boolean,
    default: true,
  },
  class: {
    type: String,
    default: '',
  },
});

const showModal = ref(false);

const displayName = computed(() => {
  if (!props.user) return props.fallback;
  if (props.user.username) {
    return props.showAt ? `@${props.user.username}` : props.user.username;
  }
  return props.user.name || props.fallback;
});

const isClickable = computed(() => {
  return props.user?.username;
});

function handleClick() {
  if (isClickable.value) {
    showModal.value = true;
  }
}
</script>

<template>
  <span>
    <button
      v-if="isClickable"
      type="button"
      @click.stop="handleClick"
      :class="[
        'text-sky-600 hover:text-sky-500 hover:underline transition cursor-pointer',
        props.class
      ]"
    >
      {{ displayName }}
    </button>
    <span v-else :class="props.class">{{ displayName }}</span>

    <UserProfileModal
      :show="showModal"
      :username="user?.username"
      @close="showModal = false"
    />
  </span>
</template>
