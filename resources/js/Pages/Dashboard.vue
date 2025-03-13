<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { usePage, Head, Link } from '@inertiajs/vue3';
defineProps({
    releases: {
        type: Array,
        required: true
    }
});
const { props } = usePage();
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Dashboard
            </h2>
        </template>
            <div class="py-12">
                <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div
                        class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                    >
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <!-- Liste des releases -->
                            <div class="">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-medium">Sorties</h3>
                                    <div v-if="props.auth.user.name === 'lynxadmin'">
                                        <Link :href="route('dashboard.addrelease')" class="ms-4">
                                            <PrimaryButton>
                                                Ajouter
                                            </PrimaryButton>
                                        </Link>
                                    </div>
                                </div>
                                <div class="mt-4 space-y-4">
                                    <div v-for="release in releases" :key="release.id">
                                        <div v-if="props.auth.user.name === release.catalog || props.auth.user.name === 'lynxadmin'"
                                            class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                            <Link :href="`/dashboard/${release.id}/edit-release`">{{ release.catalog }}
                                                <span v-if="release.name">
                                                    - {{ release.name }}
                                                </span>
                                                <span v-if="release.release_type">
                                                    - {{ release.release_type.name }}
                                                </span>
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </AuthenticatedLayout>
</template>
