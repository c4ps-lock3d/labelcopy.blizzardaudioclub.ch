<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    release: {
        type: Object,
        required: true
    },
    releaseTypes: {
        type: Array,
        required: true
    },
    releaseFormats: {
        type: Array,
        required: true
    },
    releaseTracks: {
        type: Array,
        required: true
    },
    releaseMembers: {
        type: Array,
        required: true
    },
    releaseSocials: {
        type: Array,
        required: true
    }
});

const form = useForm({
    catalog: props.release.catalog,
    name: props.release.name,
    artistName: props.release.artistName,
    artistIBAN: props.release.artistIBAN,
    artistBiography: props.release.artistBiography,
    artistWebsite: props.release.artistWebsite,
    style: props.release.style,
    price: props.release.price,
    description: props.release.description,
    CodeBarre: props.release.CodeBarre,
    cleRepartition: props.release.cleRepartition,
    credits: props.release.credits,
    remerciements: props.release.remerciements,
    remarques: props.release.remarques,
    envies: props.release.envies,
    besoinFinancement: props.release.besoinFinancement,
    sourceFinancement: props.release.sourceFinancement,
    budget: props.release.budget,
    isProduitsDerives: Boolean(props.release.isProduitsDerives),
    isBesoinSubvention: Boolean(props.release.isBesoinSubvention),
    isBesoinPromo: Boolean(props.release.isBesoinPromo),
    isBesoinDigitalMarketing: Boolean(props.release.isBesoinDigitalMarketing),
    isBesoinContacts: Boolean(props.release.isBesoinContacts),
    isActive: Boolean(props.release.isActive),

    release_type_id: props.release.release_type_id || null,
    release_format_ids: props.release.release_formats?.map(format => format.id) || [],
    reference_member_id: props.release.release_members?.findIndex(member => member.is_reference) ?? 0,
    
    socials: props.release.release_socials.map(social => ({
            id: social.id,
            link: social.link
        })),
    
    tracks: props.release.release_tracks?.length > 0 
        ? props.release.release_tracks.map(track => ({
            id: track.id,
            title: track.title,
            number: track.number,
            isSingle: Boolean(track.isSingle),
            hasClip: Boolean(track.hasClip),
            IRSC: track.IRSC || '',
            participations: props.release.release_members.map(member => ({
                member_id: member.id,
                firstname: member.firstname,
                lastname: member.lastname,
                percentage: 0, // Initialisé à 0 par défaut
            })),
        }))
        : [{
            id: null,
            title: '',
            number: 1,
            isSingle: false,
            hasClip: false,
            IRSC: '',
            participations: props.release.release_members.map(member => ({
                member_id: member.id,
                firstname: member.firstname,
                lastname: member.lastname,
                percentage: 0,
            })),
        }],

    members: props.release.release_members?.length > 0 
        ? props.release.release_members?.map(member => ({
        id: member.id,
        firstname: member.firstname,
        lastname: member.lastname,
        birth_date: member.birth_date,
        IPI: member.IPI || '',
        is_reference: Boolean(member.is_reference),
        street: member.street || '',
        city: member.city || '',
        zip_code: member.zip_code || '',
        phone_number: member.phone_number || '',
    }))
    : [{
            id: null,
            firstname: '',
            lastname: '',
            birth_date: '',
            IPI: '',
            is_reference: false,
            street: '',
            city: '',
            zip_code: '',
            phone_number: '',
        }],

    releaseTypes: props.releaseTypes,
    releaseFormats: props.releaseFormats,
    releaseTracks: props.releaseTracks,
    releaseMembers: props.releaseMembers,
    releaseSocials: props.releaseSocials,
});

const isDisabled = computed(() => !props.release.isActive);

const addNewTrack = () => {
    const nextTrackNumber = form.tracks.length + 1;
    form.tracks.push({
        id: null,
        title: '',
        number: nextTrackNumber,
        isSingle: false,
        hasClip: false,
        participations: form.members.map(member => ({
            member_id: member.id,
            firstname: member.firstname,
            lastname: member.lastname,
            percentage: 0, // Initialize percentage to 0
        })),
    });
};

const addNewMember = () => {
    // Ajouter un nouveau membre
    const newMember = {
        id: null,
        firstname: '',
        lastname: '',
        birth_date: '',
        IPI: '',
        is_reference: false,
        street: '',
        city: '',
        zip_code: '',
        phone_number: '',
    };
    form.members.push(newMember);

    // Ajouter une participation pour ce membre dans chaque track
    form.tracks.forEach(track => {
        track.participations.push({
            member_id: null, // ID null car le membre n'est pas encore sauvegardé
            firstname: newMember.firstname,
            lastname: newMember.lastname,
            percentage: 0, // Initialisé à 0 par défaut
        });
    });
};

const addNewSocial = () => {
    form.socials.push({
        id: null,
        link: 'https://',
    });
};

const updateSocialLink = (index, event) => {
    form.socials[index].link = initializeLink(event.target.value);
};
const initializeLink = (link) => {
    return link.startsWith('http://') || link.startsWith('https://') ? link : `https://${link}`;
};

const deleteTrack = (index) => {
    form.tracks.splice(index, 1);
};

const deleteMember = (index) => {
    form.members.splice(index, 1);
};

const deleteSocial = (index) => {
    form.socials.splice(index, 1);
};

let isUpdatingParticipations = false;

watch(
    () => form.members,
    (newMembers, oldMembers) => {
        if (isUpdatingParticipations) {
            return;
        }

        try {
            isUpdatingParticipations = true;

            // Si un membre a été supprimé
            if (newMembers.length < oldMembers.length) {
                const deletedIndex = oldMembers.findIndex(
                    (oldMember, index) => !newMembers[index] || oldMember.id !== newMembers[index].id
                );

                if (deletedIndex !== -1) {
                    // Mettre à jour chaque piste avec les participations mises à jour
                    form.tracks = form.tracks.map(track => ({
                        ...track,
                        participations: track.participations.filter((_, pIndex) => pIndex !== deletedIndex)
                    }));
                }
            }

            // Synchroniser les participations existantes
            form.tracks.forEach(track => {
                track.participations = form.members.map((member, index) => ({
                    member_id: member.id,
                    firstname: member.firstname,
                    lastname: member.lastname,
                    percentage: track.participations[index]?.percentage || 0
                }));
            });

        } finally {
            isUpdatingParticipations = false;
        }
    },
    { deep: true }
);

const validateMemberPercentage = (track, member) => {
    if (member.percentage > 100) {
        member.percentage = 100; // Force la valeur à 100 si elle dépasse
    }
    if (member.percentage < 0) {
        member.percentage = 0; // Force la valeur à 0 si elle est négative
    }
    const totalPercentage = track.participations.reduce((sum, member) => sum + (member.percentage || 0), 0);
    if (totalPercentage > 100) {
        alert('La somme des participations ne peut pas dépasser 100%.');
        member.percentage = 0;
    }
};

const submit = () => {
    if (form.tracks.some(track => !track.title) || form.tracks.some(track => !track.number )) {
        return;
    }
    if (form.members.some(member => !member.firstname) || form.members.some(member => !member.lastname)) {
        return;
    }
    if (form.socials.some(social => !social.link)) {
        return;
    }
    form.put(route('update-release', props.release.id));
};
</script>

<template>
    <Head :title="`Labelcopy ${form.catalog}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Dashboard > Labelcopy {{ form.catalog }}
            </h2>
        </template>

        <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="space-y-10">
        <div class="overflow-hidden bg-white shadow-lg rounded-lg dark:bg-gray-800">
            <div class="p-8">
                    <!-- Section Informations Principales -->
                    <div class="space-y-6 border-gray-700 pb-6">
                        <h3 class="text-xl font-bold text-gray-100 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mr-4 size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            Informations sur l'artiste</h3>
                        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                            <div>
                                <InputLabel for="artistName" value="Nom d'artiste" class="required text-sm font-medium" />
                                <TextInput
                                    id="artistName"
                                    type="text"
                                    class="mt-1 block w-full transition duration-150 ease-in-out"
                                    v-model="form.artistName"
                                    required
                                    autocomplete="artistName"
                                    :disabled="isDisabled"
                                />
                                <InputError class="mt-2" :message="form.errors.artistName" />
                       
                                <InputLabel for="artistBiography" value="Biographie de l’artiste" class="required text-sm font-medium mt-6" />
                                <TextArea
                                    id="artistBiography"
                                    type="text"
                                    class="mt-1 block w-full transition duration-150 ease-in-out"
                                    v-model="form.artistBiography"
                                    rows="10"
                                    required
                                    autocomplete="artistBiography"
                                />
                                <InputError class="mt-2" :message="form.errors.artistBiography" />
                            </div>
                            <div>
                                <InputLabel for="artistWebsite" value="Site web de l’artiste" class="text-sm font-medium" />
                                <TextInput
                                    id="artistWebsite"
                                    type="text"
                                    class="mt-1 block w-full transition duration-150 ease-in-out"
                                    v-model="form.artistWebsite"
                                    autocomplete="artistWebsite"
                                    placeholder="https://example.ch"
                                />
                                <InputError class="mt-2" :message="form.errors.artistWebsite" />

                                <InputLabel value="Réseaux sociaux" class="text-sm font-medium mt-6" />
                                <table class="min-w-full rounded-md overflow-hidden border-gray-700 !mt-1">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="w-full px-3 py-2.5 text-left text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap">Lien</th>
                                            <th scope="col" class="w-16 px-3 py-2.5 text-sm font-semibold bg-gray-700 text-gray-100">
                                                <button 
                                                        type="button" 
                                                        @click="addNewSocial"
                                                        class="w-9 h-9 bg-globalButtonColor text-black text-sm rounded-md hover:bg-globalButtonHoverColor transition-colors flex items-center justify-center"
                                                    >
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                    </svg>
                                                </button>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-700">
                                        <tr v-for="(social, index) in form.socials" :key="social.id || 'new'" class="bg-gray-700/50">
                                            <td class="px-1.5 py-2">
                                                <TextInput
                                                    type="text"
                                                    v-model="social.link"
                                                    @input="updateSocialLink(index, $event)"
                                                    class="w-full transition duration-150 ease-in-out"
                                                    placeholder="Lien"
                                                />
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-2">
                                                <button
                                                    v-if="form.socials.length > 0 && index === form.socials.length - 1"
                                                    type="button" 
                                                    @click="deleteSocial(index)"
                                                    class="w-9 h-9 bg-globalButtonColor text-black rounded-md hover:bg-globalButtonHoverColor transition-colors flex items-center justify-center"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-hidden bg-white shadow-lg rounded-lg dark:bg-gray-800">
                <div class="p-8">
                    <div class="space-y-6 border-gray-700 pb-6">
                        <h3 class="text-xl font-bold text-gray-100 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mr-4 size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                        Informations sur le(s) membre(s)</h3>
                        <InputLabel value="" class="text-sm font-medium" />
                        <div class="overflow-x-auto">
                            <table class="rounded-md overflow-hidden border-gray-700 !mt-1">
                                <thead>
                                    <tr>
                                        <th scope="col" class="required w-16 px-3 py-2.5 text-left text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap">Prénom</th>
                                        <th scope="col" class="required px-3 py-2.5 text-left text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap">Nom</th>
                                        <th scope="col" class="required px-3 py-2.5 text-left text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap">Date de naissance</th>
                                        <th scope="col" class="w-full px-3 py-2.5 text-left text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap">N° IPI (SUISA)</th>
                                        <th scope="col" class="w-16 px-3 py-2.5 text-sm font-semibold bg-gray-700 text-gray-100">
                                            <button 
                                                type="button" 
                                                @click="addNewMember"
                                                class="w-9 h-9 bg-globalButtonColor text-black text-sm rounded-md hover:bg-globalButtonHoverColor transition-colors flex items-center justify-center"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700">
                                    <tr v-for="(member, index) in form.members" :key="member.id || 'new'" class="bg-gray-700/50">
                                        <td class="px-1.5 py-2">
                                            <TextInput
                                                type="text"
                                                v-model="member.firstname"
                                                :class="{
                                                    '!bg-gray-700/50': member.is_reference,
                                                    'mt-1 block transition duration-150 ease-in-out': true
                                                }"
                                                placeholder="Prénom"
                                                :disabled="member.is_reference"
                                            />
                                        </td>
                                        <td class="px-1.5 py-2">
                                            <TextInput
                                                type="text"
                                                v-model="member.lastname"
                                                :class="{
                                                    '!bg-gray-700/50': member.is_reference,
                                                    'mt-1 block transition duration-150 ease-in-out': true
                                                }"
                                                placeholder="Nom"
                                                :disabled="member.is_reference"
                                            />
                                        </td>
                                        <td class="px-1.5 py-2">
                                            <TextInput
                                                type="date"
                                                v-model="member.birth_date"
                                                class="transition duration-150 ease-in-out"
                                                placeholder="Date de naissance"
                                            />
                                        </td>
                                        <td class="px-1.5 py-2">
                                            <TextInput
                                                type="text"
                                                v-model="member.IPI"
                                                class="w-full transition duration-150 ease-in-out"
                                            />
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-2">
                                            <button
                                                v-if="form.members.length > 1 && index === form.members.length - 1"
                                                type="button" 
                                                @click="deleteMember(index)"
                                                class="w-9 h-9 bg-globalButtonColor text-black rounded-md hover:bg-globalButtonHoverColor transition-colors flex items-center justify-center"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-for="(member, index) in form.members" :key="member.id">
                            <div v-if="member.is_reference">
                                <InputLabel for="" :value="`Coordonnées du membre de référence (${member.firstname} ${member.lastname})`" class="text-sm font-medium" />
                                <div class="mt-1 flex items-center p-2.5 bg-gray-700 rounded-lg transition-colors">
                                    <div class="grid grid-cols-1 gap-6 md:grid-cols-4 mb-2 w-full">
                                        <div>
                                        <InputLabel for="zip_code" value="NPA" class="required text-sm font-medium" />
                                        <TextInput
                                            id="zip_code"
                                            type="text"
                                            class="mt-1 block w-full transition duration-150 ease-in-out"
                                            v-model="member.zip_code"
                                            required
                                            autocomplete="zip_code"
                                        />
                                        <InputError class="" :message="form.errors.zip_code" />
                                        </div>
                                        <div>
                                        <InputLabel for="city" value="Ville" class="required text-sm font-medium" />
                                        <TextInput
                                            id="city"
                                            type="text"
                                            class="mt-1 block w-full transition duration-150 ease-in-out"
                                            v-model="member.city"
                                            required
                                            autocomplete="city"
                                        />
                                        <InputError class="" :message="form.errors.city" />
                                        </div>
                                        <div>
                                        <InputLabel for="street" value="Rue" class="required text-sm font-medium" />
                                        <TextInput
                                            id="street"
                                            type="text"
                                            class="mt-1 block w-full transition duration-150 ease-in-out"
                                            v-model="member.street"
                                            required
                                            autocomplete="street"
                                        />
                                        <InputError class="" :message="form.errors.street" />
                                        </div>
                                        <div>
                                        <InputLabel for="phone_number" value="Numéro de téléphone" class="required text-sm font-medium" />
                                        <TextInput
                                            id="phone_number"
                                            type="text"
                                            class="mt-1 block w-full transition duration-150 ease-in-out"
                                            v-model="member.phone_number"
                                            required
                                            autocomplete="phone_number"
                                        />
                                        <InputError class="" :message="form.errors.phone_number" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-hidden bg-white shadow-lg rounded-lg dark:bg-gray-800">
                <div class="p-8">
                    <!-- Section Types et Formats -->
                    <div class="space-y-6 border-gray-700 pb-6">
                        <h3 class="text-xl font-bold text-gray-100 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mr-4 size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 9 10.5-3m0 6.553v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 1 1-.99-3.467l2.31-.66a2.25 2.25 0 0 0 1.632-2.163Zm0 0V2.25L9 5.25v10.303m0 0v3.75a2.25 2.25 0 0 1-1.632 2.163l-1.32.377a1.803 1.803 0 0 1-.99-3.467l2.31-.66A2.25 2.25 0 0 0 9 15.553Z" />
                            </svg>
                            Informations sur la sortie</h3>
                            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                                    <div>
                                        <InputLabel for="catalog" value="N° de catalogue" class="required text-sm font-medium" />
                                        <TextInput
                                            id="catalog"
                                            type="text"
                                            class="!bg-gray-800 mt-1 block w-full transition duration-150 ease-in-out"
                                            v-model="form.catalog"
                                            required
                                            autocomplete="catalog"
                                            disabled
                                        />
                                        <InputError class="" :message="form.errors.catalog" />
                                    </div>
                                    <div>
                                        <InputLabel for="name" value="Nom de la sortie" class="required text-sm font-medium" />
                                        <TextInput
                                            id="name"
                                            type="text"
                                            class="mt-1 block w-full transition duration-150 ease-in-out"
                                            v-model="form.name"
                                            required
                                            autocomplete="name"
                                        />
                                        <InputError class="" :message="form.errors.name" />
                                    </div>
                                    <div>
                                        <InputLabel for="style" value="Style musical" class="required text-sm font-medium" />
                                        <TextInput
                                            id="style"
                                            type="text"
                                            class="mt-1 block w-full transition duration-150 ease-in-out"
                                            v-model="form.style"
                                            required
                                            autocomplete="style"
                                        />
                                        <InputError class="" :message="form.errors.style" />
                                    </div>
                                <div>
                                    <InputLabel value="Format(s)" class="required text-sm font-medium mb-1" />
                                    <div class="grid grid-cols-4 gap-2">
                                        <div v-for="format in props.releaseFormats" :key="format.id" 
                                            class="flex items-center p-2.5 bg-gray-900 rounded-lg hover:bg-gray-900/50 transition-colors">
                                            <input
                                                type="checkbox"
                                                :id="'format-' + format.id"
                                                :value="format.id"
                                                v-model="form.release_format_ids"
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                            <label :for="'format-' + format.id" class="ml-2 text-sm text-gray-100">
                                                {{ format.name }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <InputLabel value="Type" class="required text-sm font-medium mb-1" />
                                    <div class="grid grid-cols-3 gap-2">
                                        <div v-for="type in props.releaseTypes" :key="type.id" 
                                            class="flex items-center p-2.5 bg-gray-900 rounded-lg hover:bg-gray-900/50 transition-colors">
                                            <input
                                                type="radio"
                                                :id="'type-' + type.id"
                                                :value="type.id"
                                                v-model="form.release_type_id"
                                                class="border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                            <label :for="'type-' + type.id" class="ml-2 text-sm text-gray-100">
                                                {{ type.name }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <InputLabel for="price" value="Prix" class="text-sm font-medium" />
                                    <TextInput
                                        id="price"
                                        type="text"
                                        :class="{
                                                    '!bg-gray-800': props.auth.user.name !== 'lynxadmin',
                                                    'w-full mt-1 block transition duration-150 ease-in-out': true
                                                }"
                                        v-model="form.price"
                                        autocomplete="price"
                                        :disabled="props.auth.user.name !== 'lynxadmin'"
                                    />
                                    <InputError class="" :message="form.errors.price" />
                                </div>
                            </div>
                                <div class="pt-3">
                                    <InputLabel for="description" value="Description" class="required text-sm font-medium" />
                                    <TextArea
                                        id="description"
                                        type="text"
                                        class="mt-1 block w-full transition duration-150 ease-in-out"
                                        v-model="form.description"
                                        rows="5"
                                        required
                                        autocomplete="description"
                                    />
                                    <InputError class="mt-2" :message="form.errors.description" />
                                </div>
                                <InputLabel value="Tracklist" class="text-sm font-medium" />
                                <div class="overflow-x-auto !mt-1">
                                    <table class="min-w-full rounded-md overflow-hidden border-gray-700">
                                        <thead>
                                            <tr>
                                                <!-- Première ligne avec le titre qui s'étend sur plusieurs colonnes -->
                                                <td rowspan="2" scope="col" class="w-8 px-3 py-2.5 text-left text-sm font-semibold text-gray-100 bg-gray-700">#</td>
                                                <td rowspan="2" scope="col" class="required w-1/2 px-3 py-2.5 text-left text-sm font-semibold bg-gray-700 text-gray-100">Titre</td>
                                                <td rowspan="2" scope="col" class="px-3 py-2.5 text-center text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap">Single ?</td>
                                                <td rowspan="2" scope="col" class="px-3 py-2.5 text-center text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap">Vidéoclip ?</td>
                                                <td v-if="props.auth.user.name === 'lynxadmin'" rowspan="2" scope="col" class="px-3 py-2.5 text-left text-sm font-semibold bg-gray-700 text-gray-100">ISRC</td>
                                                <th scope="col" :colspan="form.members.length" class="px-3 pt-3 text-center text-sm font-semibold bg-gray-600 text-gray-100">
                                                    Clé de répartition précise des morceaux
                                                </th>
                                                <td rowspan="2" scope="col" class="px-3 py-2.5 text-center text-sm font-semibold bg-gray-700 text-gray-100">
                                                    <button 
                                                            type="button" 
                                                            @click="addNewTrack"
                                                            class="w-9 h-9 bg-globalButtonColor text-black text-sm rounded-md hover:bg-globalButtonHoverColor transition-colors flex items-center justify-center"
                                                        >
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th v-for="member in form.members" :key="member.id || 'new'" class="required px-3 py-2.5 text-center text-sm font-semibold bg-gray-600 text-gray-100 whitespace-nowrap">
                                                    {{ member.firstname.charAt(0) }}. {{ member.lastname }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-700">
                                            <tr v-for="(track, index) in form.tracks" :key="track.id || 'new'" class="bg-gray-700/50">
                                                <td class="px-3 py-2 text-white">
                                                    {{track.number}}
                                                </td>
                                                <td class="px-3 py-2">
                                                    <TextInput
                                                        type="text"
                                                        v-model="track.title"
                                                        class="w-full min-w-[300px] transition duration-150 ease-in-out"
                                                        placeholder="Titre"
                                                    />
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-2 text-center">
                                                    <input
                                                            type="checkbox"
                                                            v-model="track.isSingle"
                                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                        />                                                
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-2 text-center">
                                                    <input
                                                            type="checkbox"
                                                            v-model="track.hasClip"
                                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                    />
                                                </td>
                                                <td v-if="props.auth.user.name === 'lynxadmin'" class="px-3 py-2">
                                                    <TextInput
                                                        type="text"
                                                        v-model="track.IRSC"
                                                        class="transition duration-150 ease-in-out"
                                                    />
                                                </td>
                                                <!-- Dynamically generate percentage inputs for each member -->
                                                <td v-for="participation in track.participations" :key="participation.member_id || 'new'" class="px-3 py-2 text-center bg-gray-600/80">
                                                    <TextInput  
                                                        type="number"
                                                        v-model="participation.percentage"
                                                        class="w-20 text-center transition duration-150 ease-in-out"
                                                        placeholder="%"
                                                        min="0"
                                                        max="100"
                                                        @input="validateMemberPercentage(track, member)"
                                                    />
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-2">
                                                    <button
                                                        v-if="form.tracks.length > 1 && index === form.tracks.length - 1"
                                                        type="button" 
                                                        @click="deleteTrack(index)"
                                                        class="w-9 h-9 bg-globalButtonColor text-black rounded-md hover:bg-globalButtonHoverColor transition-colors flex items-center justify-center"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                                    <div v-if="props.auth.user.name === 'lynxadmin'"
                                    >
                                        <InputLabel for="CodeBarre" value="Code-Barre(s)" class="text-sm font-medium" />
                                        <TextInput
                                            id="CodeBarre"
                                            type="text"
                                            class="mt-1 block w-full transition duration-150 ease-in-out"
                                            v-model="form.CodeBarre"
                                            autocomplete="CodeBarre"
                                        />
                                        <InputError class="" :message="form.errors.CodeBarre" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                                    <div>
                                        <InputLabel for="credits" value="Crédits" class="required text-sm font-medium" />
                                        <TextArea
                                            id="credits"
                                            type="text"
                                            class="mt-1 block w-full transition duration-150 ease-in-out"
                                            v-model="form.credits"
                                            rows="5"
                                            required
                                            autocomplete="credits"
                                        />
                                        <InputError class="mt-2" :message="form.errors.credits" />
                                    </div>
                                    <div>
                                        <InputLabel for="remerciements" value="Remerciements" class="text-sm font-medium" />
                                        <TextArea
                                            id="remerciements"
                                            type="text"
                                            class="mt-1 block w-full transition duration-150 ease-in-out"
                                            v-model="form.remerciements"
                                            rows="5"
                                            autocomplete="remerciements"
                                        />
                                        <InputError class="mt-2" :message="form.errors.remerciements" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-hidden bg-white shadow-lg rounded-lg dark:bg-gray-800">
                        <div class="p-8">
                            <div class="space-y-6 border-gray-700 pb-6">
                                <h3 class="text-xl font-bold text-gray-100 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mr-4 size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                                </svg>
                                Aide et soutien pour...</h3>
                                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                                    <div class="flex items-center space-x-4">
                                        <input
                                            type="checkbox"
                                            v-model="form.isProduitsDerives"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                        <InputLabel for="isProduitsDerives" value="la création de produits dérivés" class="text-sm font-medium" />
                                        <InputError class="mt-2" :message="form.errors.isProduitsDerives" />
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <input
                                            type="checkbox"
                                            v-model="form.isBesoinSubvention"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                        <InputLabel for="isBesoinSubvention" value="les dossiers de demande de subventions" class="text-sm font-medium" />
                                        <InputError class="mt-2" :message="form.errors.isBesoinSubvention" />
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <input
                                            type="checkbox"
                                            v-model="form.isBesoinPromo"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                        <InputLabel for="isBesoinPromo" value="la promo" class="text-sm font-medium" />
                                        <InputError class="mt-2" :message="form.errors.isBesoinPromo" />
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <input
                                            type="checkbox"
                                            v-model="form.isBesoinDigitalMarketing"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                        <InputLabel for="isBesoinDigitalMarketing" value="faire/recourir à du digital marketing" class="text-sm font-medium" />
                                        <InputError class="mt-2" :message="form.errors.isBesoinDigitalMarketing" />
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <input
                                            type="checkbox"
                                            v-model="form.isBesoinContacts"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                        <InputLabel for="isBesoinContacts" value="mix, mastering, graphisme, vidéos, photos, merch, etc." class="text-sm font-medium" />
                                        <InputError class="mt-2" :message="form.errors.isBesoinContacts" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-hidden bg-white shadow-lg rounded-lg dark:bg-gray-800">
                        <div class="p-8">
                            <div class="space-y-6 border-gray-700 pb-6">
                                <h3 class="text-xl font-bold text-gray-100 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mr-4 size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                </svg>
                                Informations financières</h3>
                                <div>
                                    <InputLabel for="artistIBAN" value="IBAN de l'artiste" class="text-sm font-medium" />
                                    <TextInput
                                        id="artistIBAN"
                                        type="text"
                                        class="mt-1 block w-full transition duration-150 ease-in-out"
                                        v-model="form.artistIBAN"
                                        autocomplete="artistIBAN"
                                    />
                                    <InputError class="mt-2" :message="form.errors.artistIBAN" />
                                </div>
                                <div>
                                    <InputLabel for="budget" value="Budget pour le projet" class="text-sm font-medium" />
                                    <TextInput
                                        id="budget"
                                        type="text"
                                        class="mt-1 block w-full transition duration-150 ease-in-out"
                                        v-model="form.budget"
                                        autocomplete="budget"
                                    />
                                    <InputError class="mt-2" :message="form.errors.budget" />
                                </div>
                                <div>
                                    <InputLabel for="sourceFinancement" value="Source(s) pour le financement du projet" class="text-sm font-medium" />
                                    <TextArea
                                        id="sourceFinancement"
                                        type="text"
                                        class="mt-1 block w-full transition duration-150 ease-in-out"
                                        v-model="form.sourceFinancement"
                                        rows="5"
                                        autocomplete="sourceFinancement"
                                    />
                                    <InputError class="mt-2" :message="form.errors.sourceFinancement" />
                                </div>
                                <div>
                                    <InputLabel for="besoinFinancement" value="Besoin de soutien financier de la part du label ? Si oui,à quelle hauteur ?" class="text-sm font-medium" />
                                    <TextArea
                                        id="besoinFinancement"
                                        type="text"
                                        class="mt-1 block w-full transition duration-150 ease-in-out"
                                        v-model="form.besoinFinancement"
                                        rows="5"
                                        autocomplete="besoinFinancement"
                                    />
                                    <InputError class="mt-2" :message="form.errors.besoinFinancement" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-hidden bg-white shadow-lg rounded-lg dark:bg-gray-800">
                        <div class="p-8">
                            <div class="space-y-6 border-gray-700 pb-6">
                                <h3 class="text-xl font-bold text-gray-100 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mr-4 size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
                                </svg>
                                Autre chose ?</h3>
                                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                                    <div>
                                        <InputLabel for="remarques" value="Remarques" class="text-sm font-medium" />
                                        <TextArea
                                            id="remarques"
                                            type="text"
                                            class="mt-1 block w-full transition duration-150 ease-in-out"
                                            v-model="form.remarques"
                                            rows="5"
                                            autocomplete="remarques"
                                        />
                                        <InputError class="mt-2" :message="form.errors.remarques" />
                                    </div>
                                    <div>
                                        <InputLabel for="envies" value="Envies particulières" class="text-sm font-medium" />
                                        <TextArea
                                            id="envies"
                                            type="text"
                                            class="mt-1 block w-full transition duration-150 ease-in-out"
                                            v-model="form.envies"
                                            rows="5"
                                            autocomplete="envies"
                                        />
                                        <InputError class="mt-2" :message="form.errors.envies" />
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-end pt-6">
                                <PrimaryButton
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Sauvegarder
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>