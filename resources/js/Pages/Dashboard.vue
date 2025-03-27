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
            <div class="py-10">
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
                                        <th scope="col" class="px-2.5 py-3 w-1/6 text-left text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap">Catalogue</th>
                                        <th scope="col" class="px-2.5 py-3 w-1/6 text-left text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap">Artiste</th>
                                        <th scope="col" class="px-2.5 py-3 w-full text-left text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap">Titre</th>
                                        <th v-if="props.auth.user.name === 'lynxadmin'" scope="col" class="px-2.5 py-3 w-16 text-left text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap">Éditable</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700">
                                    <tr v-for="release in releases" :key="release.id" class="bg-gray-700/50 hover:bg-gray-700/40">
                                        <Link :href="route('dashboard.editrelease', release.id)" class="contents">
                                            <td  class="px-2.5 py-3">
                                                <div v-if="release.catalog">{{ release.catalog }}</div>
                                            </td>
                                            <td class="px-2.5 py-3">
                                                <div v-if="release.artistName">{{ release.artistName }}</div>
                                            </td>
                                            <td class="px-2.5 py-3">
                                                <div v-if="release.name">{{ release.name }}</div>
                                            </td>
                                        </Link>
                                        <td v-if="props.auth.user.name === 'lynxadmin'" class="px-2.5 text-center">
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    v-model="release.isActive"
                                                    @change="toggleIsActive(release)"
                                                    class="sr-only peer"
                                                />
                                                <div
                                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 rounded-full peer dark:bg-gray-700 peer-checked:bg-indigo-600 mt-1.5"
                                                ></div>
                                                <div
                                                    class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-5 mt-1.5"
                                                ></div>
                                            </label>
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
