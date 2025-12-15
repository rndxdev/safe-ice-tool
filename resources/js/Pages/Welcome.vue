<script setup>
import MarketingLayout from '@/Layouts/MarketingLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
  <MarketingLayout>
    <Head title="Safe Ice Tool" />

    <div class="min-h-screen bg-slate-950 text-slate-50">
      <!-- Hero -->
      <section class="px-4 py-16 sm:py-24">
        <div class="max-w-5xl mx-auto grid gap-10 lg:grid-cols-[3fr,2fr] items-center">
          <div>
            <p class="text-sm uppercase tracking-wide text-sky-400 mb-3">
              Ice fishing safety, powered by the community
            </p>

            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-semibold leading-tight mb-4">
              Check safe ice conditions before you step on the lake.
            </h1>

            <p class="text-base sm:text-lg text-slate-300 mb-6">
              Safe Ice Tool is a community-driven ice thickness and conditions tracker for ice fishing.
              Learn safe ice guidelines, see live reports, and help keep other anglers safe while still
              enjoying the season.
            </p>

            <div class="flex flex-wrap gap-3 mb-6">
              <Link
                :href="route('register')"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-md bg-sky-500 hover:bg-sky-400 text-sm font-medium"
              >
                Create a free account
              </Link>

              <Link
                :href="route('login')"
                class="inline-flex items-center justify-center px-5 py-2.5 rounded-md border border-slate-600 text-sm font-medium hover:bg-slate-900"
              >
                Log in
              </Link>
            </div>

            <p class="text-xs text-slate-400 max-w-lg">
              This tool does not guarantee ice safety. Always check local conditions, use proper gear,
              and make your own judgment before going on the ice.
            </p>
          </div>

          <div class="bg-slate-900/70 border border-slate-700 rounded-xl p-5 sm:p-6">
            <p class="text-sm text-slate-300 mb-4">
              Why Safe Ice Tool exists
            </p>

            <ul class="space-y-3 text-sm text-slate-300">
              <li>
                Help reduce accidents by sharing real world ice thickness reports before people
                drive or walk onto the ice.
              </li>
              <li>
                Turn scattered social media posts into structured ice safety data that is easy to
                search, filter, and trust.
              </li>
              <li>
                Build a local and regional ice fishing community that learns together, teaches
                newcomers, and respects the water.
              </li>
            </ul>

            <div class="mt-5 grid grid-cols-3 gap-3 text-center text-xs text-slate-400">
              <div class="border border-slate-700 rounded-md p-2">
                <div class="text-base text-sky-400 mb-1">
                  Live
                </div>
                <div>
                  Community ice reports
                </div>
              </div>
              <div class="border border-slate-700 rounded-md p-2">
                <div class="text-base text-sky-400 mb-1">
                  Learn
                </div>
                <div>
                  Safe ice education
                </div>
              </div>
              <div class="border border-slate-700 rounded-md p-2">
                <div class="text-base text-sky-400 mb-1">
                  Plan
                </div>
                <div>
                  Trips with better data
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Three pillars -->
      <section class="px-4 py-12 bg-slate-900/60 border-y border-slate-800">
        <div class="max-w-5xl mx-auto">
          <h2 class="text-2xl font-semibold mb-2">
            Learn. Check. Share.
          </h2>
          <p class="text-sm text-slate-300 mb-8 max-w-3xl">
            Safe Ice Tool blends education with real-time community ice reports so you can
            learn safe ice thickness guidelines, check live conditions on your favorite lakes,
            and share what you see to help other anglers.
          </p>

          <div class="grid gap-6 md:grid-cols-3">
            <div class="bg-slate-950/60 border border-slate-800 rounded-xl p-4">
              <h3 class="text-lg font-medium mb-2">
                Learn safe ice basics
              </h3>
              <p class="text-sm text-slate-300">
                Get clear, simple guidance on safe ice thickness for foot travel, snowmobiles, and
                vehicles. Understand the difference between clear ice, white ice, slush, and late season
                honeycombed ice.
              </p>
            </div>

            <div class="bg-slate-950/60 border border-slate-800 rounded-xl p-4">
              <h3 class="text-lg font-medium mb-2">
                Check live ice conditions
              </h3>
              <p class="text-sm text-slate-300">
                Browse recent crowd-sourced ice thickness reports, conditions, and notes for each lake.
                See when a report was updated, what traffic was observed, and how consistent the readings are.
              </p>
            </div>

            <div class="bg-slate-950/60 border border-slate-800 rounded-xl p-4">
              <h3 class="text-lg font-medium mb-2">
                Share with the community
              </h3>
              <p class="text-sm text-slate-300">
                Log your own ice fishing reports, record thickness measurements, and flag pressure cracks,
                slush, or sketchy spots. Help friends and local anglers make better decisions before they go.
              </p>
            </div>
          </div>
        </div>
      </section>

      <!-- How it works -->
      <section class="px-4 py-14">
        <div class="max-w-5xl mx-auto grid gap-8 md:grid-cols-[3fr,2fr]">
          <div>
            <h2 class="text-2xl font-semibold mb-3">
              How Safe Ice Tool works
            </h2>
            <p class="text-sm text-slate-300 mb-6 max-w-xl">
              The vision is simple: take the scattered ice fishing posts, texts, and stories that already
              exist and turn them into a simple map of safe ice reports backed by a real community.
            </p>

            <ol class="space-y-4 text-sm text-slate-300 list-decimal list-inside">
              <li>
                Create a free account and choose the lakes you care about most.
              </li>
              <li>
                Check live crowd-sourced ice conditions before you go: thickness, type of ice,
                slush, pressure cracks, and traffic type.
              </li>
              <li>
                After you drill holes and measure, log what you found to help others make safe
                decisions and plan their next ice trip.
              </li>
            </ol>
          </div>

          <div class="bg-slate-900/70 border border-slate-800 rounded-xl p-5 text-sm text-slate-300">
            <p class="font-medium mb-2">
              Safety first, fun always
            </p>
            <p class="mb-3">
              Ice fishing is supposed to be fun. The goal is not to scare anyone off the ice, but
              to give people better tools so they can fish longer seasons, explore new spots, and
              bring everyone home at the end of the day.
            </p>
            <p class="mb-3">
              Safe Ice Tool does not replace your own judgment, local knowledge, ice chisels,
              or safety gear. It adds one more layer of information to help you make better calls
              and protect the people who fish with you.
            </p>
            <p>
              Over time, the community can build a history of freeze-up dates, first safe ice,
              and late-season patterns that help everyone understand their lakes better.
            </p>
          </div>
        </div>
      </section>

      <!-- Community + CTA -->
      <section class="px-4 py-14 bg-slate-900/70 border-t border-slate-800">
        <div class="max-w-4xl mx-auto text-center">
          <h2 class="text-2xl font-semibold mb-3">
            Build a smarter ice fishing community, one report at a time.
          </h2>
          <p class="text-sm text-slate-300 mb-6">
            Safe Ice Tool is built for people who love hardwater season and care about safety.
            Educate new anglers, protect your local community, and still enjoy every flag, bite, and sunrise.
          </p>

          <div class="flex flex-wrap gap-3 justify-center mb-4">
            <Link
              :href="route('register')"
              class="inline-flex items-center justify-center px-6 py-2.5 rounded-md bg-sky-500 hover:bg-sky-400 text-sm font-medium"
            >
              Get started in a few minutes
            </Link>

            <Link
              :href="route('login')"
              class="inline-flex items-center justify-center px-6 py-2.5 rounded-md border border-slate-600 text-sm font-medium hover:bg-slate-950"
            >
              Already have an account
            </Link>
          </div>

          <p class="text-xs text-slate-500">
            Always check local regulations and conditions. No app can guarantee safe ice.
          </p>
        </div>
      </section>
    </div>
  </MarketingLayout>
</template>
