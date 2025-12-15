<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import axios from "axios";
import { reactive } from "vue";

const props = defineProps({
    trips: {
        type: Array,
        required: true,
    },
});

// Cache share links per trip so you can show them in the UI after creation
const shareLinks = reactive({});

const formatDate = (dateStr) => {
    if (!dateStr) return "No date set";

    // Expecting YYYY-MM-DD from Laravel / DB
    const parts = String(dateStr).split("-");
    if (parts.length !== 3) return dateStr;

    const [year, month, day] = parts;
    return `${Number(month)}/${Number(day)}/${year}`;
};

function buildShareText(trip, shareUrl) {
    const lakeName = trip.lake?.name ? trip.lake.name : "a lake";
    const region = trip.lake?.region ? ` (${trip.lake.region})` : "";
    const date = trip.trip_date ? formatDate(trip.trip_date) : "soon";
    const time = trip.time_of_day ? ` ${trip.time_of_day}` : "";
    const min = trip.min_thickness_inches
        ? ` Min ice: ${trip.min_thickness_inches} in.`
        : "";

    const avoid = [];
    if (trip.avoid_slush) avoid.push("slush");
    if (trip.avoid_pressure_cracks) avoid.push("pressure cracks");

    const avoidStr = avoid.length ? ` Avoiding: ${avoid.join(", ")}.` : "";
    const species = trip.target_species
        ? ` Target: ${trip.target_species}.`
        : "";
    const linkStr = shareUrl ? ` ${shareUrl}` : "";

    return `Fishing plan: ${lakeName}${region} on ${date}${time}.${min}${avoidStr}${species} Posted from Safe Ice Tool.${linkStr}`;
}

async function copyToClipboard(text) {
    if (navigator.clipboard?.writeText) {
        await navigator.clipboard.writeText(text);
        return true;
    }
    return false;
}

async function ensureShareLink(trip) {
    // Use cached link if we already created it
    if (shareLinks[trip.id]) return shareLinks[trip.id];

    // If backend already includes a share_url on each trip, use it
    if (trip.share_url) {
        shareLinks[trip.id] = trip.share_url;
        return trip.share_url;
    }

    // Create share link via server (token stored in DB)
    // Route must exist: trips.share.create
    const res = await axios.post(route("trips.share.create", trip.id));
    const url = res?.data?.shareUrl || null;

    if (url) {
        shareLinks[trip.id] = url;
    }

    return url;
}

async function shareTrip(trip) {
    try {
        const shareUrl = await ensureShareLink(trip);
        const text = buildShareText(trip, shareUrl);

        if (navigator.share) {
            await navigator.share({
                title: "My fishing plan",
                text,
                url: shareUrl || undefined,
            });
            return;
        }

        const copied = await copyToClipboard(text);
        if (copied) {
            alert("Copied trip summary (with link) to clipboard.");
            return;
        }

        prompt("Copy this text:", text);
    } catch (e) {
        alert("Could not create a share link. Please try again.");
    }
}

async function shareToX(trip) {
    try {
        const shareUrl = await ensureShareLink(trip);
        const text = buildShareText(trip, shareUrl);
        const url = `https://x.com/intent/tweet?text=${encodeURIComponent(
            text
        )}`;
        window.open(url, "_blank", "noopener,noreferrer");
    } catch (e) {
        alert("Could not create a share link. Please try again.");
    }
}

async function copyShareLink(trip) {
    try {
        const shareUrl = await ensureShareLink(trip);
        if (!shareUrl) {
            alert("Could not create a share link. Please try again.");
            return;
        }

        const copied = await copyToClipboard(shareUrl);
        if (copied) {
            alert("Share link copied.");
            return;
        }

        prompt("Copy this link:", shareUrl);
    } catch (e) {
        alert("Could not create a share link. Please try again.");
    }
}
</script>

<template>
    <AuthenticatedLayout>
        <Head title="My fishing plan" />

        <div class="max-w-4xl mx-auto p-4 space-y-4">
            <header>
                <h1 class="text-2xl font-semibold mb-1">My fishing plan</h1>
                <p class="text-sm text-gray-500">
                    Trips you are planning on your favorite lakes, with your own
                    safety preferences.
                </p>
            </header>

            <div class="flex justify-end mb-2">
                <Link
                    :href="route('trips.create')"
                    class="px-3 py-2 rounded-md bg-sky-500 hover:bg-sky-400 text-sm text-white"
                >
                    Plan a new trip
                </Link>
            </div>

            <div v-if="trips.length === 0" class="text-sm text-gray-500">
                You do not have any planned trips yet. Add a trip using your
                favorite lakes to start building your season.
            </div>

            <ul v-else class="space-y-3">
                <li
                    v-for="trip in trips"
                    :key="trip.id"
                    class="border rounded-lg bg-white p-3"
                >
                    <div class="flex justify-between items-baseline">
                        <div>
                            <div class="text-sm text-gray-500">
                                <Link
                                    v-if="trip.lake"
                                    :href="route('lakes.show', trip.lake.slug)"
                                    class="text-sky-600 hover:underline"
                                >
                                    {{ trip.lake.name }}
                                </Link>
                                <span v-else> Lake unavailable </span>
                            </div>

                            <div
                                v-if="trip.lake && trip.lake.region"
                                class="text-xs text-gray-500"
                            >
                                {{ trip.lake.region }}
                            </div>
                        </div>

                        <div class="text-xs text-gray-600 text-right">
                            <div>
                                {{ formatDate(trip.trip_date) }}
                            </div>
                            <div v-if="trip.time_of_day">
                                {{ trip.time_of_day }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-2 text-xs text-gray-700 space-y-1">
                        <div v-if="trip.min_thickness_inches">
                            Minimum thickness preference:
                            {{ trip.min_thickness_inches }} inches
                        </div>

                        <div
                            v-if="
                                trip.avoid_slush || trip.avoid_pressure_cracks
                            "
                        >
                            Conditions to avoid:
                            <span v-if="trip.avoid_slush">slush</span>
                            <span
                                v-if="
                                    trip.avoid_slush &&
                                    trip.avoid_pressure_cracks
                                "
                                >,
                            </span>
                            <span v-if="trip.avoid_pressure_cracks"
                                >pressure cracks</span
                            >
                        </div>

                        <div v-if="trip.target_species">
                            Target species:
                            {{ trip.target_species }}
                        </div>
                    </div>

                    <div v-if="trip.notes" class="mt-2 text-sm text-gray-800">
                        {{ trip.notes }}
                    </div>

                    <div
                        v-if="shareLinks[trip.id]"
                        class="mt-2 text-xs text-gray-500 break-all"
                    >
                        Share link: {{ shareLinks[trip.id] }}
                    </div>

                    <div class="mt-3 flex items-center justify-between gap-2">
                        <div class="flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="px-3 py-2 border border-gray-300 rounded text-xs hover:bg-gray-50"
                                @click="shareTrip(trip)"
                            >
                                Share
                            </button>

                            <button
                                type="button"
                                class="px-3 py-2 border border-gray-300 rounded text-xs hover:bg-gray-50"
                                @click="shareToX(trip)"
                            >
                                Share to X
                            </button>

                            <button
                                type="button"
                                class="px-3 py-2 border border-gray-300 rounded text-xs hover:bg-gray-50"
                                @click="copyShareLink(trip)"
                            >
                                Copy link
                            </button>
                        </div>

                        <Link
                            :href="route('trips.edit', trip.id)"
                            class="text-xs text-sky-600 hover:underline"
                        >
                            Edit trip
                        </Link>

                        <Link
                            :href="route('trips.post.create', trip.id)"
                            class="px-3 py-2 border border-gray-300 rounded text-xs hover:bg-gray-50"
                        >
                            Create post
                        </Link>
                    </div>
                </li>
            </ul>

            <div class="text-xs text-gray-500 border rounded-lg bg-white p-3">
                Tip: Share creates a public, tokenized link stored in the
                database. People without an account can view it.
            </div>
        </div>
    </AuthenticatedLayout>
</template>
