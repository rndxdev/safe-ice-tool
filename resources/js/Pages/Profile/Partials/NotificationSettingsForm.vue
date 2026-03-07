<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm, usePage } from '@inertiajs/vue3';

const user = usePage().props.auth.user;
const settings = user.notification_settings || {};

const form = useForm({
    notification_settings: {
        mentions: settings.mentions ?? true,
    },
});

function submit() {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
}
</script>

<template>
    <section id="notifications">
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Notification Settings
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Choose how you want to be notified about activity.
            </p>
        </header>

        <form @submit.prevent="submit" class="mt-6 space-y-6">
            <div class="rounded-lg border border-gray-200 p-4">
                <h3 class="text-sm font-medium text-gray-900 mb-3">In-App Notifications</h3>
                <p class="text-xs text-gray-500 mb-4">Control which notifications appear in your notification center.</p>

                <div class="space-y-3">
                    <label class="flex items-center">
                        <Checkbox v-model:checked="form.notification_settings.mentions" />
                        <span class="ms-2 text-sm text-gray-600">Notify me when someone @mentions me</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
