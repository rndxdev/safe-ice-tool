<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';

const bgUrl =
  'https://images.unsplash.com/photo-1670951724934-7c7076ff3f11?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D';
</script>

<template>
  <div class="min-h-screen relative">
    <div
      class="absolute inset-0 bg-center bg-cover"
      :style="{ backgroundImage: `url('${bgUrl}')` }"
    ></div>

    <div class="absolute inset-0 bg-black/45"></div>

    <div class="relative flex min-h-screen flex-col items-center pt-6 sm:justify-center sm:pt-0">
      <div>
        <Link href="/">
          <ApplicationLogo class="h-20 w-20 fill-current text-gray-200" />
        </Link>
      </div>

      <div class="mt-6 w-full overflow-hidden bg-white/95 px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg">
        <slot />
      </div>
    </div>
  </div>
</template>
