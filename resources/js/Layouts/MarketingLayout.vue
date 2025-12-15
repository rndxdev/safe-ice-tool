<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="min-h-screen bg-slate-950 text-slate-50 flex flex-col">
    <header class="border-b border-slate-800">
      <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
        <Link
          :href="route('welcome')"
          class="text-sm font-semibold tracking-tight"
        >
          Safe Ice Tool
        </Link>

        <nav class="flex items-center gap-3 text-xs sm:text-sm">
          <Link
            v-if="route().has('login')"
            :href="route('login')"
            class="hover:text-sky-400"
          >
            Log in
          </Link>

          <Link
            v-if="route().has('register')"
            :href="route('register')"
            class="px-3 py-1.5 rounded-md bg-sky-500 hover:bg-sky-400 text-slate-950 font-medium"
          >
            Sign up
          </Link>
        </nav>
      </div>
    </header>

    <main class="flex-1">
      <slot />
    </main>
  </div>
</template>
