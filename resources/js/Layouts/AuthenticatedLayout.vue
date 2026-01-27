<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const currentYear = new Date().getFullYear();
</script>

<template>
  <div>
    <div class="min-h-screen bg-slate-100 flex flex-col">
      <nav class="border-b border-slate-700 bg-slate-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="flex h-16 justify-between">
            <div class="flex">
              <div class="flex shrink-0 items-center">
                <Link :href="route('dashboard')" class="flex items-center gap-2">
                  <ApplicationLogo class="block h-9 w-auto fill-current text-sky-400" />
                  <span class="text-sm font-semibold text-white tracking-tight hidden lg:block">Safe Ice Tool</span>
                </Link>
              </div>

              <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                <NavLink
                  :href="route('dashboard')"
                  :active="route().current('dashboard')"
                >
                  Dashboard
                </NavLink>

                <NavLink
                  :href="route('lakes.index')"
                  :active="route().current('lakes.index') || route().current('lakes.show')"
                >
                  Lakes
                </NavLink>

                <NavLink
                  :href="route('lakes.create')"
                  :active="route().current('lakes.create')"
                >
                  Add Lake
                </NavLink>

                <NavLink
                  :href="route('reports.mine')"
                  :active="route().current('reports.mine')"
                >
                  My reports
                </NavLink>

                <NavLink
                  :href="route('trips.index')"
                  :active="route().current('trips.index') || route().current('trips.create')"
                >
                   My Trips
                </NavLink>
                <NavLink
                  :href="route('lakes.mine')"
                  :active="route().current('lakes.mine')"
                >
                   My Lakes
                </NavLink>
              </div>
            </div>

            <div class="hidden sm:ms-6 sm:flex sm:items-center">
              <div class="relative ms-3">
                <Dropdown align="right" width="48">
                  <template #trigger>
                    <span class="inline-flex rounded-md">
                      <button
                        type="button"
                        class="inline-flex items-center rounded-md border border-transparent bg-slate-800 px-3 py-2 text-sm font-medium leading-4 text-slate-300 transition duration-150 ease-in-out hover:text-white hover:bg-slate-700 focus:outline-none"
                      >
                        {{ $page.props.auth.user.name }}

                        <svg
                          class="-me-0.5 ms-2 h-4 w-4"
                          xmlns="http://www.w3.org/2000/svg"
                          viewBox="0 0 20 20"
                          fill="currentColor"
                        >
                          <path
                            fill-rule="evenodd"
                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                          />
                        </svg>
                      </button>
                    </span>
                  </template>

                  <template #content>
                    <DropdownLink :href="route('profile.edit')">
                      Profile
                    </DropdownLink>
                    <DropdownLink
                      :href="route('logout')"
                      method="post"
                      as="button"
                    >
                      Log Out
                    </DropdownLink>
                  </template>
                </Dropdown>
              </div>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
              <button
                @click="showingNavigationDropdown = !showingNavigationDropdown"
                class="inline-flex items-center justify-center rounded-md p-2 text-slate-400 transition duration-150 ease-in-out hover:bg-slate-800 hover:text-white focus:bg-slate-800 focus:text-white focus:outline-none"
              >
                <svg
                  class="h-6 w-6"
                  stroke="currentColor"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <path
                    :class="{
                      hidden: showingNavigationDropdown,
                      'inline-flex': !showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16"
                  />
                  <path
                    :class="{
                      hidden: !showingNavigationDropdown,
                      'inline-flex': showingNavigationDropdown,
                    }"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div
          :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
          class="sm:hidden bg-slate-900"
        >
          <div class="space-y-1 pb-3 pt-2">
            <ResponsiveNavLink
              :href="route('dashboard')"
              :active="route().current('dashboard')"
            >
              Dashboard
            </ResponsiveNavLink>

            <ResponsiveNavLink
              :href="route('lakes.index')"
              :active="route().current('lakes.index') || route().current('lakes.show')"
            >
              Lakes
            </ResponsiveNavLink>

            <ResponsiveNavLink
              :href="route('lakes.create')"
              :active="route().current('lakes.create')"
            >
              Add Lake
            </ResponsiveNavLink>

            <ResponsiveNavLink
              :href="route('reports.mine')"
              :active="route().current('reports.mine')"
            >
              My reports
            </ResponsiveNavLink>

            <ResponsiveNavLink
              :href="route('trips.index')"
              :active="route().current('trips.index') || route().current('trips.create')"
            >
              My Trips
            </ResponsiveNavLink>
          </div>

          <div class="border-t border-slate-700 pb-1 pt-4">
            <div class="px-4">
              <div class="text-base font-medium text-white">
                {{ $page.props.auth.user.name }}
              </div>
              <div class="text-sm font-medium text-slate-400">
                {{ $page.props.auth.user.email }}
              </div>
            </div>

            <div class="mt-3 space-y-1">
              <ResponsiveNavLink :href="route('profile.edit')">
                Profile
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('logout')"
                method="post"
                as="button"
              >
                Log Out
              </ResponsiveNavLink>
            </div>
          </div>
        </div>
      </nav>

      <header class="bg-white shadow" v-if="$slots.header">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <main class="flex-1">
        <slot />
      </main>

      <footer class="border-t border-slate-800 bg-slate-900">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
          <div class="grid gap-6 md:grid-cols-3">
            <div>
              <div class="flex items-center gap-2">
                <ApplicationLogo class="block h-8 w-auto fill-current text-sky-400" />
                <span class="text-sm font-semibold text-white tracking-tight">Safe Ice Tool</span>
              </div>
              <p class="mt-2 text-sm text-slate-300">
                Community-driven ice reports and trip planning for safer days on the lake.
              </p>
            </div>
            <div>
              <h4 class="text-sm font-semibold text-white">Safety first</h4>
              <p class="mt-2 text-sm text-slate-300">
                Ice conditions change fast. Always verify thickness locally and follow official guidance.
              </p>
            </div>
            <div>
              <h4 class="text-sm font-semibold text-white">Resources</h4>
              <div class="mt-2 space-y-2 text-sm text-slate-300">
                <a href="https://www.weather.gov" target="_blank" rel="noopener noreferrer"
                  class="block hover:text-white">National Weather Service</a>
                <a href="https://www.redcross.org/get-help/how-to-prepare-for-emergencies/types-of-emergencies/winter-storm.html"
                  target="_blank" rel="noopener noreferrer"
                  class="block hover:text-white">Red Cross Winter Storm Safety</a>
              </div>
            </div>
          </div>
          <div class="mt-6 border-t border-slate-800 pt-4 text-xs text-slate-400">
            © {{ currentYear }} Safe Ice Tool. All rights reserved.
          </div>
        </div>
      </footer>
    </div>
  </div>
</template>
