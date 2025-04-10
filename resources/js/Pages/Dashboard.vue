<script setup>
import { ref, computed } from 'vue'; // Ajoutez cette ligne
import axios from 'axios';  // Ajoutez cette ligne
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Success from '@/Components/Success.vue'
import { usePage, Head, Link } from '@inertiajs/vue3';
defineProps({
    releases: {
        type: Array,
        required: true
    },
    members: {  // Ajoutez cette propriété
        type: Array,
        required: true
    }
});
const { props } = usePage();

const releases = ref(props.releases.map(release => ({
    ...release,
    isActive: Boolean(release.isActive),
})));

const toggleIsActive = async (release) => {
    try {
        await axios.put(route('update-release-status', release.id), {
            isActive: release.isActive,
        });
        
        // Mettre à jour l'état local
        const index = releases.value.findIndex(r => r.id === release.id);
        if (index !== -1) {
            releases.value[index].isActive = release.isActive;
        }
        // Afficher le message de succès
        usePage().props.flash = {
            success: {
                message: `${release.catalog} ${release.isActive ? ': édition activée pour l\'utilisateur' : ': édition désactivée pour l\'utilisateur'}`
            }
        };
        
    } catch (error) {
        console.error('Erreur lors de la mise à jour de l\'état de la release :', error);
        release.isActive = !release.isActive;
    }
};

// Fonction générique pour le tri
const sortData = (data, key, order) => {
    if (!key) return data;

    return [...data].sort((a, b) => {
        const valueA = a[key] || '';
        const valueB = b[key] || '';

        if (valueA < valueB) return order === 'asc' ? -1 : 1;
        if (valueA > valueB) return order === 'asc' ? 1 : -1;
        return 0;
    });
};

// Propriétés pour le tri des releases
const releaseSortKey = ref('');
const releaseSortOrder = ref('asc');

// Propriétés pour le tri des members
const memberSortKey = ref('');
const memberSortOrder = ref('asc');

// Méthode de tri unifiée pour les deux tableaux
const sortedData = computed(() => ({
    members: sortData(props.members, memberSortKey.value, memberSortOrder.value),
    releases: sortData(releases.value, releaseSortKey.value, releaseSortOrder.value)
}));

// Fonction de tri unifiée
const sortBy = (key, type) => {
    if (type === 'releases') {
        if (releaseSortKey.value === key) {
            releaseSortOrder.value = releaseSortOrder.value === 'asc' ? 'desc' : 'asc';
        } else {
            releaseSortKey.value = key;
            releaseSortOrder.value = 'asc';
        }
    } else if (type === 'members') {
        if (memberSortKey.value === key) {
            memberSortOrder.value = memberSortOrder.value === 'asc' ? 'desc' : 'asc';
        } else {
            memberSortKey.value = key;
            memberSortOrder.value = 'asc';
        }
    }
};

const successMessage = computed(() => usePage().props.flash?.success?.message);
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
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-1 mb-6">
                        <Success :message="successMessage" />
                        <div
                            class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                        >
                            <div class="p-6 text-gray-900 dark:text-gray-100">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-medium flex items-center mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 mr-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                        </svg>

                                Labelcopy</h3>
                                    <div v-if="props.auth.user.name === 'lynxadmin'">
                                        <Link :href="route('dashboard.addrelease')" class="ms-4">
                                            <PrimaryButton>
                                                Ajouter
                                            </PrimaryButton>
                                        </Link>
                                    </div>
                                </div>
                                <!-- Liste des releases -->
                                <div class="overflow-hidden rounded-md border border-gray-300 dark:border-gray-600 !mt-4">
                                    <table class="min-w-full">
                                        <thead>
                                            <tr class="bg-gray-100 dark:bg-gray-700">
                                                <th @click="sortBy('catalog', 'releases')" scope="col" class="px-2.5 py-3 w-1/4 text-left text-sm font-semibold text-gray-800 dark:text-gray-100 cursor-pointer select-none">
                                                    Catalogue
                                                    <span v-if="releaseSortKey === 'catalog'">
                                                        {{ releaseSortOrder === 'asc' ? '↑' : '↓' }}
                                                    </span>
                                                </th>
                                                <th @click="sortBy('artistName', 'releases')" scope="col" class="px-2.5 py-3 w-1/6 text-left text-sm font-semibold text-gray-800 dark:text-gray-100 whitespace-nowrap select-none">
                                                    Artiste
                                                    <span v-if="releaseSortKey === 'artistName'">
                                                        {{ releaseSortOrder === 'asc' ? '↑' : '↓' }}
                                                    </span>
                                                </th>
                                                <th @click="sortBy('name', 'releases')" scope="col" class="px-2.5 py-3 w-full text-left text-sm font-semibold text-gray-800 dark:text-gray-100 whitespace-nowrap select-none">
                                                    Titre
                                                    <span v-if="releaseSortKey === 'name'">
                                                        {{ releaseSortOrder === 'asc' ? '↑' : '↓' }}
                                                    </span>
                                                </th>
                                                <th v-if="props.auth.user.name === 'lynxadmin'" @click="sortBy('isActive', 'releases')" scope="col" class="px-2.5 py-3 w-16 text-left text-sm font-semibold text-gray-800 dark:text-gray-100 whitespace-nowrap select-none">
                                                    Statut
                                                    <span v-if="releaseSortKey === 'isActive'">
                                                        {{ releaseSortOrder === 'asc' ? '↑' : '↓' }}
                                                    </span>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-if="sortedData.releases.length === 0" class="bg-white dark:bg-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition border-t border-gray-200 dark:border-gray-600">
                                                <td colspan="4" class="px-2.5 py-3 text-center text-gray-500 dark:text-gray-400">
                                                    Aucune sortie existante pour le moment. Cliquer sur le bouton "Ajouter" pour en créer une nouvelle.
                                                </td>
                                            </tr>
                                            <tr v-else v-for="release in sortedData.releases" :key="release.id" class="bg-white dark:bg-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition border-t border-gray-200 dark:border-gray-600">
                                                <Link :href="route('dashboard.editrelease', release.id)" class="contents">
                                                    <td class="px-2.5 py-3 text-gray-900 dark:text-gray-100">
                                                        <div v-if="release.catalog">{{ release.catalog }}</div>
                                                    </td>
                                                    <td class="px-2.5 py-3 text-gray-900 dark:text-gray-100">
                                                        <div v-if="release.artistName">{{ release.artistName }}</div>
                                                        <div v-else class="italic text-gray-500">Cliquer pour ajouter</div>
                                                    </td>
                                                    <td class="px-2.5 py-3 text-gray-900 dark:text-gray-100">
                                                        <div v-if="release.name">{{ release.name }}</div>
                                                        <div v-else class="italic text-gray-500">Cliquer pour ajouter</div>
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
                        <div
                            class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800 mt-6"
                        >
                            <div v-if="props.auth.user.name === 'lynxadmin'">
                                <div class="p-6 text-gray-900 dark:text-gray-100">
                                    <h3 class="text-lg font-medium flex items-center mb-8">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 mr-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                        </svg>
                                        Liste des membres
                                    </h3>
                                    <div class="overflow-hidden rounded-md border border-gray-300 dark:border-gray-600">
                                        <table class="min-w-full">
                                            <thead>
                                                <tr class="bg-gray-100 dark:bg-gray-700">
                                                    <th @click="sortBy('firstname', 'members')" scope="col" class="px-2.5 py-3 w-1/4 text-left text-sm font-semibold text-gray-800 dark:text-gray-100 cursor-pointer select-none">
                                                        Prénom
                                                        <span v-if="memberSortKey === 'firstname'">
                                                            {{ memberSortOrder === 'asc' ? '↑' : '↓' }}
                                                        </span>
                                                    </th>
                                                    <th @click="sortBy('lastname', 'members')" scope="col" class="px-2.5 py-3 w-1/4 text-left text-sm font-semibold text-gray-800 dark:text-gray-100 cursor-pointer select-none">
                                                        Nom
                                                        <span v-if="memberSortKey === 'lastname'">
                                                            {{ memberSortOrder === 'asc' ? '↑' : '↓' }}
                                                        </span>
                                                    </th>
                                                    <th @click="sortBy('birth_date', 'members')" scope="col" class="px-2.5 py-3 w-1/4 text-left text-sm font-semibold text-gray-800 dark:text-gray-100 cursor-pointer select-none">
                                                        Date de naissance
                                                        <span v-if="memberSortKey === 'birth_date'">
                                                            {{ memberSortOrder === 'asc' ? '↑' : '↓' }}
                                                        </span>
                                                    </th>
                                                    <th @click="sortBy('IPI', 'members')" scope="col" class="px-2.5 py-3 w-1/4 text-left text-sm font-semibold text-gray-800 dark:text-gray-100 cursor-pointer select-none">
                                                        IPI
                                                        <span v-if="memberSortKey === 'IPI'">
                                                            {{ memberSortOrder === 'asc' ? '↑' : '↓' }}
                                                        </span>
                                                    </th>
                                                    <th @click="sortBy('is_reference', 'members')" scope="col" class="px-2.5 py-3 w-1/4 text-left text-sm font-semibold text-gray-800 dark:text-gray-100 cursor-pointer select-none">
                                                        Rôle
                                                        <span v-if="memberSortKey === 'is_reference'">
                                                            {{ memberSortOrder === 'asc' ? '↑' : '↓' }}
                                                        </span>
                                                    </th>
                                                    <th @click="sortBy('email', 'members')" scope="col" class="px-2.5 py-3 w-1/4 text-left text-sm font-semibold text-gray-800 dark:text-gray-100 cursor-pointer select-none">
                                                        E-mail
                                                        <span v-if="memberSortKey === 'email'">
                                                            {{ memberSortOrder === 'asc' ? '↑' : '↓' }}
                                                        </span>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-if="sortedData.members.length === 0" class="bg-white dark:bg-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition border-t border-gray-200 dark:border-gray-600">
                                                    <td colspan="5" class="px-2.5 py-3 text-center text-gray-500 dark:text-gray-400">
                                                        Aucun membre existant.
                                                    </td>
                                                </tr>
                                                <tr v-else v-for="member in sortedData.members" :key="member.id" class="bg-white dark:bg-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition border-t border-gray-200 dark:border-gray-600">
                                                    <td class="px-2.5 py-3 text-gray-900 dark:text-gray-100">
                                                        {{ member.firstname }}
                                                    </td>
                                                    <td class="px-2.5 py-3 text-gray-900 dark:text-gray-100">
                                                        {{ member.lastname }}
                                                    </td>
                                                    <td class="px-2.5 py-3 text-gray-900 dark:text-gray-100">
                                                        {{ member.birth_date }}
                                                    </td>
                                                    <td class="px-2.5 py-3 text-gray-900 dark:text-gray-100">
                                                        {{ member.IPI }}
                                                    </td>
                                                    <td class="px-2.5 py-3 text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                                        {{ member.is_reference ? 'Membre référent' : 'Membre standard' }}
                                                    </td>
                                                    <td v-if="member.email" class="px-2.5 py-3 text-gray-900 dark:text-gray-100 whitespace-nowrap">
                                                        {{ member.email }}
                                                    </td>
                                                    <td v-else class="px-2.5 py-3 text-gray-900 dark:text-gray-100">
                                                        -
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>  
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </AuthenticatedLayout>
</template>
