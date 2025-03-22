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
                            <!-- Liste des releases -->
                            <table class="min-w-full rounded-md overflow-hidden border-gray-700 !mt-4">
                                <thead>
                                    <tr>
                                        <th scope="col" class="px-1.5 py-2 text-left text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap">Catalogue</th>
                                        <th scope="col" class="px-1.5 py-2 text-left text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap">Artiste</th>
                                        <th scope="col" class="px-1.5 py-2 text-left text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap">Titre</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700">
                                    <tr v-for="release in releases" :key="release.id" class="bg-gray-700/50">
                                        <td  class="px-1.5 py-2">
                                            <div v-if="release.catalog">{{ release.catalog }}</div>
                                        </td>
                                        <td class="px-1.5 py-2">
                                            <div v-if="release.artistName">{{ release.artistName }}</div>
                                        </td>
                                        <td class="px-1.5 py-2">
                                            <div v-if="release.name">{{ release.name }}</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
    </AuthenticatedLayout>
</template>
