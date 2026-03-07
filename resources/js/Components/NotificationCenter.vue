<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const isOpen = ref(false);
const loading = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);
let pollInterval = null;

async function fetchNotifications() {
  try {
    const response = await fetch('/notifications', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
    if (response.ok) {
      const data = await response.json();
      notifications.value = data.notifications;
      unreadCount.value = data.unread_count;
    }
  } catch (e) {
    console.error('Failed to fetch notifications', e);
  }
}

async function fetchUnreadCount() {
  try {
    const response = await fetch('/notifications/unread-count', {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
    if (response.ok) {
      const data = await response.json();
      unreadCount.value = data.unread_count;
    }
  } catch (e) {
    // Silent fail for polling
  }
}

async function markAsRead(notification) {
  if (notification.read) return;

  try {
    await fetch(`/notifications/${notification.id}/read`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
    });

    notification.read = true;
    unreadCount.value = Math.max(0, unreadCount.value - 1);
  } catch (e) {
    console.error('Failed to mark as read', e);
  }
}

async function markAllAsRead() {
  try {
    await fetch('/notifications/read-all', {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
    });

    notifications.value.forEach(n => n.read = true);
    unreadCount.value = 0;
  } catch (e) {
    console.error('Failed to mark all as read', e);
  }
}

function handleNotificationClick(notification) {
  markAsRead(notification);
  if (notification.url) {
    isOpen.value = false;
    router.visit(notification.url);
  }
}

function formatTime(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  const now = new Date();
  const diffMs = now - d;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return 'Just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  if (diffDays < 7) return `${diffDays}d ago`;
  return d.toLocaleDateString();
}

function toggleOpen() {
  isOpen.value = !isOpen.value;
  if (isOpen.value && notifications.value.length === 0) {
    loading.value = true;
    fetchNotifications().finally(() => loading.value = false);
  }
}

function handleClickOutside(e) {
  const el = document.getElementById('notification-center');
  if (el && !el.contains(e.target)) {
    isOpen.value = false;
  }
}

onMounted(() => {
  fetchUnreadCount();
  // Poll every 60 seconds
  pollInterval = setInterval(fetchUnreadCount, 60000);
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval);
  document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
  <div id="notification-center" class="relative">
    <!-- Bell Icon Button -->
    <button
      type="button"
      @click.stop="toggleOpen"
      class="relative inline-flex items-center justify-center rounded-md p-2 text-slate-400 hover:text-white hover:bg-slate-700 focus:outline-none transition"
    >
      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>

      <!-- Unread Badge -->
      <span
        v-if="unreadCount > 0"
        class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown -->
    <Transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        class="absolute right-0 mt-2 w-80 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
      >
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
          <h3 class="text-sm font-semibold text-slate-900">Notifications</h3>
          <button
            v-if="unreadCount > 0"
            type="button"
            @click="markAllAsRead"
            class="text-xs text-sky-600 hover:text-sky-500"
          >
            Mark all read
          </button>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="p-8 text-center">
          <div class="animate-spin h-6 w-6 border-2 border-sky-500 border-t-transparent rounded-full mx-auto"></div>
        </div>

        <!-- Empty -->
        <div v-else-if="notifications.length === 0" class="p-8 text-center">
          <svg class="h-10 w-10 text-slate-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
          <p class="text-sm text-slate-500">No notifications yet</p>
        </div>

        <!-- Notification List -->
        <ul v-else class="max-h-96 overflow-y-auto divide-y divide-slate-100">
          <li
            v-for="n in notifications"
            :key="n.id"
            @click="handleNotificationClick(n)"
            :class="[
              'px-4 py-3 cursor-pointer hover:bg-slate-50 transition',
              !n.read && 'bg-sky-50'
            ]"
          >
            <div class="flex items-start gap-3">
              <div
                :class="[
                  'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center',
                  n.type === 'mention' ? 'bg-sky-100 text-sky-600' : 'bg-slate-100 text-slate-600'
                ]"
              >
                <svg v-if="n.type === 'mention'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                </svg>
                <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
              </div>
              <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-slate-900">{{ n.title }}</p>
                <p class="text-xs text-slate-600 truncate">{{ n.message }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ formatTime(n.created_at) }}</p>
              </div>
              <div v-if="!n.read" class="flex-shrink-0">
                <span class="block h-2 w-2 rounded-full bg-sky-500"></span>
              </div>
            </div>
          </li>
        </ul>

        <!-- Footer -->
        <div class="border-t border-slate-100 px-4 py-2">
          <a
            href="/profile#notifications"
            class="block text-center text-xs text-slate-500 hover:text-slate-700"
          >
            Notification settings
          </a>
        </div>
      </div>
    </Transition>
  </div>
</template>
