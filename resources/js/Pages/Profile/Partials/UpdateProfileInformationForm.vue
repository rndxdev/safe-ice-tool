<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;
const visibility = user.profile_visibility || {};

const form = useForm({
    name: user.name,
    username: user.username || '',
    bio: user.bio || '',
    location: user.location || '',
    profile_visibility: {
        show_name: visibility.show_name ?? true,
        show_location: visibility.show_location ?? false,
    },
    email: user.email,
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile information and email address.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="username" value="Username" />

                <div class="relative mt-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">@</span>
                    <TextInput
                        id="username"
                        type="text"
                        class="block w-full pl-8"
                        v-model="form.username"
                        autocomplete="username"
                        placeholder="your_handle"
                    />
                </div>
                <p class="mt-1 text-xs text-gray-500">Letters, numbers, dashes, and underscores only. This is how you appear in comments.</p>

                <InputError class="mt-2" :message="form.errors.username" />
            </div>

            <div>
                <InputLabel for="bio" value="Bio" />

                <textarea
                    id="bio"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    v-model="form.bio"
                    rows="3"
                    maxlength="500"
                    placeholder="Tell others a bit about yourself..."
                ></textarea>
                <p class="mt-1 text-xs text-gray-500">{{ form.bio?.length || 0 }}/500 characters</p>

                <InputError class="mt-2" :message="form.errors.bio" />
            </div>

            <div>
                <InputLabel for="location" value="Location" />

                <TextInput
                    id="location"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.location"
                    placeholder="e.g., Minnesota, USA"
                    maxlength="100"
                />

                <InputError class="mt-2" :message="form.errors.location" />
            </div>

            <div class="rounded-lg border border-gray-200 p-4">
                <h3 class="text-sm font-medium text-gray-900 mb-3">Profile Privacy</h3>
                <p class="text-xs text-gray-500 mb-4">Choose what others can see when they view your profile.</p>

                <div class="space-y-3">
                    <label class="flex items-center">
                        <Checkbox v-model:checked="form.profile_visibility.show_name" />
                        <span class="ms-2 text-sm text-gray-600">Show my name</span>
                    </label>

                    <label class="flex items-center">
                        <Checkbox v-model:checked="form.profile_visibility.show_location" />
                        <span class="ms-2 text-sm text-gray-600">Show my location</span>
                    </label>
                </div>
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    A new verification link has been sent to your email address.
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
