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
const releases = props.releases.map(release => ({
    ...release,
    isActive: Boolean(release.isActive), // Convertit en booléen
}));

const toggleIsActive = async (release) => {
    try {
        await axios.put(route('update-release-status', release.id), {
            isActive: release.isActive,
        });
    } catch (error) {
        console.error('Erreur lors de la mise à jour de l\'état de la release :', error);
    }
};
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
                                        <th scope="col" class="px-2.5 py-3 text-left text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap">Catalogue</th>
                                        <th scope="col" class="px-2.5 py-3 text-left text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap">Artiste</th>
                                        <th scope="col" class="px-2.5 py-3 w-full text-left text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap">Titre</th>
                                        <th scope="col" class="px-2.5 py-3 w-16 text-left text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap"></th>
                                        <th v-if="props.auth.user.name === 'lynxadmin'" scope="col" class="px-2.5 py-3 w-16 text-left text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700">
                                    <tr v-for="release in releases" :key="release.id" class="bg-gray-700/50">
                                        <td  class="px-2.5 py-3">
                                            <div v-if="release.catalog">{{ release.catalog }}</div>
                                        </td>
                                        <td class="px-2.5 py-3">
                                            <div v-if="release.artistName">{{ release.artistName }}</div>
                                        </td>
                                        <td class="px-2.5 py-3">
                                            <div v-if="release.name">{{ release.name }}</div>
                                        </td>
                                        <td v-if="props.auth.user.name === 'lynxadmin'" class="px-2.5 py-3 text-center">
                                            <input
                                                type="checkbox"
                                                v-model="release.isActive"
                                                @change="toggleIsActive(release)"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                        </td>
                                        <td class="px-2.5 py-3">
                                            <Link :href="route('dashboard.editrelease', release.id)">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 mr-2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                            </Link>
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
