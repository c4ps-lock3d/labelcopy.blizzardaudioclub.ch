<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { ref } from 'vue';

const releaseFormatError = ref(null);
const releaseTypeError = ref(null);

const scrollToError = () => {
    if (form.errors.release_format_ids && releaseFormatError.value) {
        releaseFormatError.value.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    if (form.errors.release_type_id && releaseTypeError.value) {
        releaseTypeError.value.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
};

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
    // releaseSocials: {
    //     type: Array,
    //     required: true
    // }
});

const form = useForm({
    catalog: props.release.catalog,
    name: props.release.name,
    artistName: props.release.artistName,
    facebook: props.release.facebook,
    instagram: props.release.instagram,
    tiktok: props.release.tiktok,
    youtube: props.release.youtube,
    bandcamp: props.release.bandcamp,
    applemusic: props.release.applemusic,
    spotify: props.release.spotify,
    soundcloud: props.release.soundcloud,
    artistIBAN: props.release.artistIBAN,
    artistBiography: props.release.artistBiography,
    artistWebsite: props.release.artistWebsite,
    style: props.release.style,
    release_date: props.release.release_date,
    description: props.release.description,
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
    besoinContacts: props.release.besoinContacts,
    isActive: Boolean(props.release.isActive),

    release_type_id: props.release.release_type_id || null,
    release_format_ids: props.release.release_formats?.map(format => format.id) || [],
    reference_member_id: props.release.release_members?.findIndex(member => member.is_reference) ?? 0,
    
    CodeBarre: props.release.release_formats?.reduce((acc, format) => {
        acc[format.id] = format.pivot?.CodeBarre || ''; // Utilisez le code-barres existant ou une chaîne vide
        return acc;
    }, {}),

    price: props.release.release_formats?.reduce((acc, format) => {
        const price = format.pivot?.price;
        acc[format.id] = price !== null && price !== undefined ? Number(price) : '';
        return acc;
    }, {}),

    // socials: props.release.release_socials.map(social => ({
    //         id: social.id,
    //         link: social.link
    //     })),
    
    tracks: props.release.release_tracks?.length > 0 
        ? props.release.release_tracks.map(track => ({
            id: track.id,
            title: track.title,
            number: track.number,
            isSingle: Boolean(track.isSingle),
            hasClip: Boolean(track.hasClip),
            IRSC: track.IRSC || '',
            release_date_single: track.release_date_single || '',
            participations: track.release_members?.map(member => ({
                member_id: member.id,
                firstname: member.firstname,
                lastname: member.lastname,
                percentage: Number(member.pivot?.percentage || 0), // Utilisez la valeur du pivot
            })),
        }))
        : [{
            id: null,
            title: '',
            number: 1,
            isSingle: false,
            hasClip: false,
            IRSC: '',
            release_date_single: '',
            participations: props.release.release_members.map(member => ({
                member_id: member.id,
                firstname: member.firstname,
                lastname: member.lastname,
                percentage: 100,
            })),
        }],

    members: props.release.release_members?.length > 0 
        ? props.release.release_members?.map(member => ({
        id: member.id,
        firstname: member.firstname || '',
        lastname: member.lastname || '',
        birth_date: member.birth_date || '',
        IPI: member.IPI || '',
        is_reference: Boolean(member.is_reference),
        street: member.street || '',
        city: member.city || '',
        country: member.country || '',
        zip_code: member.zip_code || '',
        phone_number: member.phone_number || '',
        email: member.email || '',
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
            country: '',
            zip_code: '',
            phone_number: '',
            email: '',
        }],

    releaseTypes: props.releaseTypes,
    releaseFormats: props.releaseFormats,
    releaseTracks: props.releaseTracks,
    releaseMembers: props.releaseMembers,
    //releaseSocials: props.releaseSocials,
});

// Constante de désactivation d'input, si le label désactive le formulaire de l'artiste.
const isDisabled = computed(() => {
    return !props.release.isActive && props.auth.user.name !== 'lynxadmin';
});

// Permet d'afficher l'entête "Date de sortie" du tableau "Liste des titres" seulement si
// au moins un titre de la liste est un single.
const hasSingleTrack = computed(() => {
    return form.tracks.some(track => track.isSingle);
});

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
    form.tracks.forEach(track => {
        track.participations = form.members.map((member, index) => ({
            member_id: member.id,
            firstname: member.firstname,
            lastname: member.lastname,
            percentage: track.participations[index]?.percentage || 0
        }));
        // Si un seul membre, définir son pourcentage à 100%
        if (form.members.length === 1) {
            track.participations[0].percentage = 100;
        }
        if (form.members.length === 2) {
            track.participations[0].percentage = 50;
            track.participations[1].percentage = 50;
        }
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
        country: '',
        email: '',
        zip_code: '',
        phone_number: '',
    };
    form.members.push(newMember);

    // Ajouter une participation pour ce membre dans chaque track
    form.tracks.forEach(track => {
        track.participations.push({
            member_id: newMember.id, // ID null car le membre n'est pas encore sauvegardé
            firstname: newMember.firstname,
            lastname: newMember.lastname,
            percentage: 0, // Initialisé à 0 par défaut
        });
    });
};

// const addNewSocial = () => {
//     form.socials.push({
//         id: null,
//         link: '',
//     });
// };

// const updateSocialLink = (index, event) => {
//     form.socials[index].link = event.target.value;
// };

const deleteTrack = (index) => {
    form.tracks.splice(index, 1);
};

const deleteMember = (index) => {
    form.members.splice(index, 1);
};

// const deleteSocial = (index) => {
//     form.socials.splice(index, 1);
// };

// Permet d'ajouter les membres au tableau des titres.
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
                // Si un seul membre, définir son pourcentage à 100%
                if (form.members.length === 1) {
                    track.participations[0].percentage = 100;
                }
                if (form.members.length === 2) {
                    track.participations[0].percentage = 50;
                    track.participations[1].percentage = 50;
                }
            });

        } finally {
            isUpdatingParticipations = false;
        }
    },
    { deep: true }
);

watch(
    () => form.release_format_ids,
    (newFormats) => {
        // Ajouter un champ CodeBarre pour les nouveaux formats sélectionnés
        newFormats.forEach(formatId => {
            if (!form.CodeBarre[formatId]) {
                form.CodeBarre[formatId] = ''; // Initialiser avec une chaîne vide
            }
            if (!form.price[formatId]) {
                form.price[formatId] = ''; // Initialiser avec une chaîne vide
            }
        });

        // Supprimer les champs CodeBarre pour les formats désélectionnés
        Object.keys(form.CodeBarre).forEach(formatId => {
            if (!newFormats.includes(Number(formatId))) {
                delete form.CodeBarre[formatId];
            }
        });
        Object.keys(form.price).forEach(formatId => {
            if (!newFormats.includes(Number(formatId))) {
                delete form.price[formatId];
            }
        });
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

const validateFormats = () => {
    if (form.release_format_ids.length === 0) {
        form.errors.release_format_ids = 'Vous devez sélectionner au moins un format.';
        return false;
    }
    form.errors.release_format_ids = null; // Réinitialiser l'erreur si un format est sélectionné
    return true;
};

const validateTypes = () => {
    if (!form.release_type_id) {
        form.errors.release_type_id = 'Vous devez sélectionner un type.';
        return false;
    }
    form.errors.release_type_id = null; // Réinitialiser l'erreur si un type est sélectionné
    return true;
};

const submit = () => {
    // Nettoyer les dates de sortie single pour les pistes non-singles
    form.tracks.forEach(track => {
        if (!track.isSingle) {
            track.release_date_single = '';
        }
    });

    if (!form.isBesoinContacts) {
        form.besoinContacts = null;
    }

    if (!validateFormats()) {
        scrollToError(); // Faire défiler vers l'erreur si la validation échoue
        return; // Arrêter la soumission
    }
    if (!validateTypes()) {
        scrollToError(); // Faire défiler vers l'erreur si la validation échoue
        return; // Arrêter la soumission
    }

    if (form.tracks.some(track => !track.title) || form.tracks.some(track => !track.number )) {
        return;
    }
    if (form.members.some(member => !member.firstname) || form.members.some(member => !member.lastname)) {
        return;
    }
    // if (form.socials.some(social => !social.link)) {
    //     return;
    // }
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
            <Link
                :href="route('dashboard')"
            >
                Dashboard
            </Link>
             > Labelcopy ({{ form.catalog }})
            </h2>
        </template>

        <div class="py-10">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div v-if="isDisabled" class="mb-10 p-4 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                L'accès au mode écriture a été désactivé par le label. Si une modification est nécessaire, merci de nous contacter par e-mail.
            </div>
        </div>
        <form @submit.prevent="submit" class="space-y-10">
        <div class="bg-white shadow-lg rounded-lg dark:bg-gray-800">
            <div class="p-6">
                    <!-- Section Informations Principales -->
                        <!-- Ajouter une alerte si le formulaire est désactivé -->
                        <div class="space-y-6 border-gray-700 text-gray-900 dark:text-gray-100 pb-6">
                        <h3 class="text-lg font-medium flex items-center mb-4">
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
                                    rows="15"
                                    required
                                    autocomplete="artistBiography"
                                    :disabled="isDisabled"
                                />
                                <InputError class="mt-2" :message="form.errors.artistBiography" />
                            </div>
                            <div>
                                <InputLabel for="liens" value="Liens" class="text-sm font-medium mb-1" />

                                <div class="space-y-3">
                                    <div class="flex items-center gap-4">
                                        <TextInput
                                            id="artistWebsite"
                                            type="text"
                                            class="flex-1 transition duration-150 ease-in-out"
                                            v-model="form.artistWebsite"
                                            autocomplete="artistWebsite"
                                            placeholder="Site web"
                                            :disabled="isDisabled"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.artistWebsite" />
                                    <div class="flex items-center gap-4">
                                        <TextInput
                                            id="facebook"
                                            type="text"
                                            class="flex-1 transition duration-150 ease-in-out"
                                            v-model="form.facebook"
                                            autocomplete="facebook"
                                            placeholder="Facebook"
                                            :disabled="isDisabled"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.facebook" />
                                    <div class="flex items-center gap-4">
                                        <TextInput
                                            id="instagram"
                                            type="text"
                                            class="flex-1 transition duration-150 ease-in-out"
                                            v-model="form.instagram"
                                            autocomplete="instagram"
                                            placeholder="Instagram"
                                            :disabled="isDisabled"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.instagram" />
                                    <div class="flex items-center gap-4">
                                        <TextInput
                                            id="tiktok"
                                            type="text"
                                            class="flex-1 transition duration-150 ease-in-out"
                                            v-model="form.tiktok"
                                            autocomplete="tiktok"
                                            placeholder="Tik Tok"
                                            :disabled="isDisabled"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.tiktok" />
                                    <div class="flex items-center gap-4">
                                        <TextInput
                                            id="youtube"
                                            type="text"
                                            class="flex-1 transition duration-150 ease-in-out"
                                            v-model="form.youtube"
                                            autocomplete="youtube"
                                            placeholder="Youtube"
                                            :disabled="isDisabled"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.youtube" />
                                    <div class="flex items-center gap-4">
                                        <TextInput
                                            id="bandcamp"
                                            type="text"
                                            class="flex-1 transition duration-150 ease-in-out"
                                            v-model="form.bandcamp"
                                            autocomplete="bandcamp"
                                            placeholder="Bandcamp"
                                            :disabled="isDisabled"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.bandcamp" />
                                    <div class="flex items-center gap-4">
                                        <TextInput
                                            id="applemusic"
                                            type="text"
                                            class="flex-1 transition duration-150 ease-in-out"
                                            v-model="form.applemusic"
                                            autocomplete="applemusic"
                                            placeholder="Apple Music"
                                            :disabled="isDisabled"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.applemusic" />
                                    <div class="flex items-center gap-4">
                                        <TextInput
                                            id="spotify"
                                            type="text"
                                            class="flex-1 transition duration-150 ease-in-out"
                                            v-model="form.spotify"
                                            autocomplete="spotify"
                                            placeholder="Spotify"
                                            :disabled="isDisabled"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.spotify" />
                                    <div class="flex items-center gap-4">
                                        <TextInput
                                            id="soundcloud"
                                            type="text"
                                            class="flex-1 transition duration-150 ease-in-out"
                                            v-model="form.soundcloud"
                                            autocomplete="soundcloud"
                                            placeholder="Soundcloud"
                                            :disabled="isDisabled"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.soundcloud" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-hidden bg-white shadow-lg rounded-lg dark:bg-gray-800">
                <div class="p-8">
                    <div class="space-y-6 border-gray-700 text-gray-900 dark:text-gray-100 pb-6">
                        <h3 class="text-lg font-medium flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mr-4 size-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                        Informations sur le(s) membre(s)</h3>
                        <InputLabel value="Liste des membres" class="required text-sm font-medium" />
                        <div class="overflow-hidden rounded-md border border-gray-300 dark:border-gray-600 !mt-1 !mb-8">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="bg-gray-100 dark:bg-gray-700">
                                        <th scope="col" class="w-16 px-3 py-2.5 text-sm font-semibold text-gray-800 dark:text-gray-100">
                                            <button 
                                                type="button" 
                                                @click="addNewMember"
                                                class="w-8 h-8 bg-indigo-600 text-white text-sm rounded-md border border-indigo-800 hover:bg-indigo-700 transition-colors flex items-center justify-center"
                                                :disabled="isDisabled"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                            </button>
                                        </th>
                                        <th scope="col" class="required w-16 px-3 py-2.5 text-left text-sm font-semibold text-gray-800 dark:text-gray-100 whitespace-nowrap">
                                            Prénom
                                        </th>
                                        <th scope="col" class="required px-3 py-2.5 text-left text-sm font-semibold text-gray-800 dark:text-gray-100 whitespace-nowrap">
                                            Nom
                                        </th>
                                        <th scope="col" class="required px-3 py-2.5 text-left text-sm font-semibold text-gray-800 dark:text-gray-100 whitespace-nowrap">
                                            Date de naissance
                                        </th>
                                        <th scope="col" class="required w-full px-3 py-2.5 text-left text-sm font-semibold text-gray-800 dark:text-gray-100 whitespace-nowrap">
                                            N° IPI (SUISA)
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                    <tr v-for="(member, index) in form.members" :key="member.id || 'new'" class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                        <td class="whitespace-nowrap px-3 py-2">
                                            <button
                                                v-if="form.members.length > 1 && !member.is_reference"
                                                type="button" 
                                                @click="deleteMember(index)"
                                                class="w-8 h-8 bg-red-500/10 border border-red-500/20 rounded-md text-red-400 transition-colors flex items-center justify-center"
                                                :disabled="isDisabled"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        </td>
                                        <td class="px-1.5 py-2">
                                            <TextInput
                                                type="text"
                                                v-model="member.firstname"
                                                :class="{
                                                    '!bg-gray-700/10': member.is_reference,
                                                    'mt-1 block transition duration-150 ease-in-out': true
                                                }"
                                                placeholder="Prénom"
                                                required
                                                :disabled="member.is_reference || isDisabled"
                                            />
                                        </td>
                                        <td class="px-1.5 py-2">
                                            <TextInput
                                                type="text"
                                                v-model="member.lastname"
                                                :class="{
                                                    '!bg-gray-700/10': member.is_reference,
                                                    'mt-1 block transition duration-150 ease-in-out': true
                                                }"
                                                placeholder="Nom"
                                                required
                                                :disabled="member.is_reference || isDisabled"
                                            />
                                        </td>
                                        <td class="px-1.5 py-2">
                                            <TextInput
                                                type="date"
                                                v-model="member.birth_date"
                                                class="transition duration-150 ease-in-out"
                                                placeholder="jj.mm.aaaa"
                                                required
                                                :disabled="isDisabled"
                                            />
                                            <InputError class="mt-2" :message="form.errors[`members.${index}.birth_date`] || ''" />
                                        </td>
                                        <td class="px-1.5 py-2">
                                            <TextInput
                                                type="text"
                                                v-model="member.IPI"
                                                class="w-full transition duration-150 ease-in-out"
                                                required
                                                :disabled="isDisabled"
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-for="(member, index) in form.members" :key="member.id">
                            <div v-if="member.is_reference">
                                <div for="coordonnées" class="text-md font-medium">Coordonnées du membre de référence ({{ member.firstname}} {{ member.lastname}})</div>
                                <div class="mt-4 flex items-center rounded-lg transition-colors">
                                    <div class="grid grid-cols-1 gap-6 md:grid-cols-4 w-full">
                                        <div>
                                            <InputLabel for="street" value="Rue et N°" class="required text-sm font-medium" />
                                            <TextInput
                                                id="street"
                                                type="text"
                                                class="mt-1 block w-full transition duration-150 ease-in-out flex-grow"
                                                v-model="member.street"
                                                required
                                                autocomplete="street"
                                                :disabled="isDisabled"
                                            />
                                            <InputError class="" :message="form.errors.street" />
                                        </div>
                                        <div>
                                            <InputLabel for="zip_code" value="NPA" class="required text-sm font-medium" />
                                            <TextInput
                                                id="zip_code"
                                                type="text"
                                                class="mt-1 block w-full transition duration-150 ease-in-out"
                                                v-model="member.zip_code"
                                                required
                                                autocomplete="zip_code"
                                                :disabled="isDisabled"
                                            />
                                            <InputError class="" :message="form.errors.zip_code" />
                                        </div>
                                        <div>
                                            <InputLabel for="city" value="Ville" class="required text-sm font-medium" />
                                            <TextInput
                                                id="city"
                                                type="text"
                                                class="mt-1 block w-full transition duration-150 ease-in-out flex-grow"
                                                v-model="member.city"
                                                required
                                                autocomplete="city"
                                                :disabled="isDisabled"
                                            />
                                            <InputError class="" :message="form.errors.city" />
                                        </div>
                                        <div>
                                            <InputLabel for="country" value="Pays" class="required text-sm font-medium" />
                                            <TextInput
                                                id="country"
                                                type="text"
                                                class="mt-1 block w-full transition duration-150 ease-in-out flex-grow"
                                                v-model="member.country"
                                                required
                                                autocomplete="country"
                                                :disabled="isDisabled"
                                            />
                                            <InputError class="" :message="form.errors.country" />
                                        </div>
                                        <div>
                                            <InputLabel for="phone_number" value="Numéro de téléphone" class="required text-sm font-medium whitespace-nowrap" />
                                            <TextInput
                                                id="phone_number"
                                                type="text"
                                                class="mt-1 block w-full transition duration-150 ease-in-out"
                                                v-model="member.phone_number"
                                                required
                                                autocomplete="phone_number"
                                                :disabled="isDisabled"
                                            />
                                            <InputError class="" :message="form.errors.phone_number" />
                                        </div>
                                        <div>
                                            <InputLabel for="email" value="E-mail" class="required text-sm font-medium" />
                                            <TextInput
                                                id="email"
                                                type="text"
                                                class="!bg-gray-700/10 mt-1 block w-full transition duration-150 ease-in-out flex-grow"
                                                v-model="member.email"
                                                required
                                                autocomplete="email"
                                                disabled
                                            />
                                            <InputError class="" :message="form.errors.email" />
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
                    <div class="space-y-6 border-gray-700 text-gray-900 dark:text-gray-100 pb-6">
                        <h3 class="text-lg font-medium flex items-center">
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
                                        class="!bg-gray-700/10 mt-1 block w-full transition duration-150 ease-in-out"
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
                                        :disabled="isDisabled"
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
                                        :disabled="isDisabled"
                                    />
                                    <InputError class="" :message="form.errors.style" />
                                </div>
                                <div>
                                    <InputLabel for="release_date" value="Date de sortie" class="required text-sm font-medium" />
                                    <TextInput
                                                type="date"
                                                v-model="form.release_date"
                                                class="mt-1  w-full transition duration-150 ease-in-out"
                                                placeholder="jj.mm.aaaa"
                                                required
                                                :disabled="isDisabled"
                                            />
                                    <InputError class="" :message="form.errors.release_date" />
                                </div>
                                <div ref="releaseTypeError">
                                    <InputLabel value="Type" class="required text-sm font-medium" />
                                    <div class="grid grid-cols-3 gap-2">
                                        <div v-for="type in props.releaseTypes" :key="type.id" 
                                            class="mt-1 flex items-center p-2.5 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                            <input
                                                type="radio"
                                                :id="'type-' + type.id"
                                                :value="type.id"
                                                v-model="form.release_type_id"
                                                class="mt-1 border-gray-300 text-indigo-600 focus:ring-indigo-500 m"
                                                :disabled="isDisabled"
                                            />
                                            <label :for="'type-' + type.id" class="ml-2 text-sm">
                                                {{ type.name }}
                                            </label>
                                        </div>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.release_type_id" />
                                </div>
                                <div ref="releaseFormatError">
                                    <InputLabel value="Format(s)" class="required text-sm font-medium mb-1" />
                                    <div class="grid grid-cols-4 gap-2">
                                        <div v-for="format in props.releaseFormats" :key="format.id" 
                                            class="flex items-center p-2.5 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                            <input
                                                type="checkbox"
                                                :id="'format-' + format.id"
                                                :value="format.id"
                                                v-model="form.release_format_ids"
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                :disabled="isDisabled"
                                            />
                                            <label :for="'format-' + format.id" class="ml-2 text-sm">
                                                {{ format.name }}
                                            </label>
                                        </div>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.release_format_ids" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                                <div v-for="formatId in form.release_format_ids" :key="formatId" class="mt-2">
                                    <InputLabel :for="'barcode-' + formatId" :value="`Code-barres sortie format ${props.releaseFormats.find(f => f.id === formatId)?.name}`" class="text-sm font-medium" />
                                    <TextInput
                                        :id="'barcode-' + formatId"
                                        type="text"
                                        :class="{
                                            'mt-1 block w-full transition duration-150 ease-in-out': true,
                                            '!bg-gray-700/10': props.auth.user.name !== 'lynxadmin'
                                        }"
                                        v-model="form.CodeBarre[formatId]"
                                        :placeholder="(!form.CodeBarre[formatId] && props.auth.user.name !== 'lynxadmin') 
                                            ? 'Sera défini par le label' 
                                            : 'Entrez le code-barres'"
                                        :disabled="isDisabled || props.auth.user.name !== 'lynxadmin'"
                                    />
                                    <InputError class="mt-2" :message="form.errors[`barcodes.${formatId}`]" />
                                    <InputLabel :for="'price-' + formatId" :value="`Prix sortie format ${props.releaseFormats.find(f => f.id === formatId)?.name}`" class="text-sm font-medium mt-2" />
                                    <TextInput
                                        :id="'price-' + formatId"
                                        type="number"
                                        :class="{
                                            'mt-1 block w-full transition duration-150 ease-in-out': true,
                                            '!bg-gray-700/10': props.auth.user.name !== 'lynxadmin'
                                        }"
                                        v-model="form.price[formatId]"
                                        :placeholder="(!form.price[formatId] && props.auth.user.name !== 'lynxadmin') 
                                            ? 'Sera défini par le label' 
                                            : 'Entrez le prix'"
                                        :disabled="isDisabled || props.auth.user.name !== 'lynxadmin'"
                                    />
                                    <InputError class="mt-2" :message="form.errors[`price.${formatId}`]" />
                                </div>
                            </div>
                                <div>
                                    <InputLabel for="description" value="Description" class="required text-sm font-medium" />
                                    <TextArea
                                        id="description"
                                        type="text"
                                        class="mt-1 block w-full transition duration-150 ease-in-out"
                                        v-model="form.description"
                                        rows="5"
                                        required
                                        autocomplete="description"
                                        :disabled="isDisabled"
                                    />
                                    <InputError class="mt-2" :message="form.errors.description" />
                                </div>
                                <InputLabel value="Liste des titres" class="required text-sm font-medium" />
                                <div class="overflow-hidden rounded-md border border-gray-300 dark:border-gray-600 !mt-1">
                                    <table class="min-w-full">
                                        <thead>
                                            <tr>
                                                <td scope="col" class="w-10 px-3 py-2.5 text-center text-sm font-semibold text-gray-800 dark:text-gray-100 bg-gray-100 dark:bg-gray-700">
                                                    <button 
                                                        type="button" 
                                                        @click="addNewTrack"
                                                        class="w-8 h-8 bg-indigo-600 text-white text-sm border border-indigo-800 rounded-md hover:bg-indigo-700 transition-colors flex items-center justify-center"
                                                        :disabled="isDisabled"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                        </svg>
                                                    </button>
                                                </td>
                                                <td scope="col" class="w-8 px-3 py-2.5 text-left text-sm font-semibold text-gray-800 dark:text-gray-100 bg-gray-100 dark:bg-gray-700">
                                                    #
                                                </td>
                                                <td scope="col" class="required w-96 px-3 py-2.5 text-left text-sm font-semibold text-gray-800 dark:text-gray-100 bg-gray-100 dark:bg-gray-700">
                                                    Titre
                                                </td>
                                                <td v-if="props.auth.user.name === 'lynxadmin'" scope="col" class="w-44 px-3 py-2.5 text-left text-sm font-semibold text-gray-800 dark:text-gray-100 bg-gray-100 dark:bg-gray-700">
                                                    ISRC
                                                </td>
                                                <td scope="col" class="w-24 px-3 py-2.5 text-center text-sm font-semibold text-gray-800 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 whitespace-nowrap">
                                                    Vidéoclip ?
                                                </td>
                                                <td scope="col" class="w-24 px-3 py-2.5 text-center text-sm font-semibold text-gray-800 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 whitespace-nowrap">
                                                    Single ?
                                                </td>
                                                <td  scope="col" class="px-3 py-2.5 text-left text-sm font-semibold text-gray-800 dark:text-gray-100 bg-gray-100 dark:bg-gray-700 whitespace-nowrap">
                                                    <div v-if="hasSingleTrack">Date de sortie</div>
                                                </td>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                            <tr v-for="(track, index) in form.tracks" :key="track.id || 'new'" class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                                <td class="whitespace-nowrap px-3 py-2">
                                                    <button
                                                        v-if="form.tracks.length > 1 && index === form.tracks.length - 1"
                                                        type="button" 
                                                        @click="deleteTrack(index)"
                                                        class="w-8 h-8 bg-red-500/10 border border-red-500/20 rounded-md text-red-400 transition-colors flex items-center justify-center"
                                                        :disabled="isDisabled"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                        </svg>
                                                    </button>
                                                </td>
                                                <td class="w-8 px-3 py-2 text-gray-900 dark:text-gray-100">
                                                    {{ track.number }}
                                                </td>
                                                <td class="px-3 py-2">
                                                    <TextInput
                                                        type="text"
                                                        v-model="track.title"
                                                        class="w-96 min-w-[300px] transition duration-150 ease-in-out"
                                                        placeholder="Titre"
                                                        required
                                                        :disabled="isDisabled"
                                                    />
                                                </td>
                                                <td v-if="props.auth.user.name === 'lynxadmin'" class="px-3 py-2">
                                                    <TextInput
                                                        type="text"
                                                        v-model="track.IRSC"
                                                        class="w-44 transition duration-150 ease-in-out"
                                                        placeholder="ISRC"
                                                    />
                                                </td>
                                                <td class="w-24 whitespace-nowrap px-3 py-2 text-center">
                                                    <input
                                                        type="checkbox"
                                                        v-model="track.hasClip"
                                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                        :disabled="isDisabled"
                                                    />
                                                </td>
                                                <td class="w-24 whitespace-nowrap px-3 py-2 text-center">
                                                    <input
                                                        type="checkbox"
                                                        v-model="track.isSingle"
                                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                        :disabled="isDisabled"
                                                    />
                                                </td>
                                                <td class="px-3 py-2">
                                                    <div class="min-h-[38px]"> <!-- Hauteur minimale pour maintenir l'espacement -->
                                                        <TextInput
                                                            v-if="track.isSingle"
                                                            type="date"
                                                            v-model="track.release_date_single"
                                                            class="transition duration-150 ease-in-out"
                                                            :disabled="isDisabled"
                                                        />
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Deuxième tableau : Clé de répartition -->
                                <InputLabel value="Clé de répartition des morceaux" class="required text-sm font-medium mt-8" />
                                <div class="overflow-hidden rounded-md border border-gray-300 dark:border-gray-600 !mt-1">
                                    <table class="min-w-full">
                                        <thead>
                                            <tr class="bg-gray-100 dark:bg-gray-700">
                                                <td scope="col" class="w-8 px-3 py-2.5 text-left text-sm font-semibold text-gray-800 dark:text-gray-100">
                                                    #
                                                </td>
                                                <td scope="col" class="w-96 px-3 py-2.5 text-left text-sm font-semibold text-gray-800 dark:text-gray-100">
                                                    Titre
                                                </td>
                                                <template v-for="member in form.members" :key="member.id">
                                                    <th scope="col" class="required px-3 py-2.5 text-center text-sm font-semibold text-gray-800 dark:text-gray-100 whitespace-nowrap">
                                                        {{ member.firstname.charAt(0) }}. {{ member.lastname }}
                                                    </th>
                                                </template>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                                            <tr v-for="(track, index) in form.tracks" :key="track.id" class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                                <td class="px-3 py-2 text-gray-900 dark:text-gray-100">
                                                    {{ track.number }}
                                                </td>
                                                <td class="px-3 py-2 text-gray-900 dark:text-gray-100">
                                                    {{ track.title }}
                                                </td>
                                                <td v-for="participation in track.participations" :key="participation.member_id" class="px-3 py-2 text-center bg-gray-200/70 dark:bg-gray-500/70">
                                                    <TextInput  
                                                        type="number"
                                                        v-model="participation.percentage"
                                                        class="w-20 text-center transition duration-150 ease-in-out"
                                                        placeholder="%"
                                                        min="0"
                                                        max="100"
                                                        @input="validateMemberPercentage(track, participation)"
                                                        :disabled="isDisabled"
                                                    />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
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
                                            :disabled="isDisabled"
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
                                            :disabled="isDisabled"
                                        />
                                        <InputError class="mt-2" :message="form.errors.remerciements" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-hidden bg-white shadow-lg rounded-lg dark:bg-gray-800">
                        <div class="p-8">
                            <div class="space-y-6 border-gray-700 text-gray-900 dark:text-gray-100 pb-6">
                                <h3 class="text-lg font-medium flex items-center">
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
                                            :disabled="isDisabled"
                                        />
                                        <InputLabel for="isProduitsDerives" value="la création de produits dérivés (merch)" class="text-sm font-medium" />
                                        <InputError class="mt-2" :message="form.errors.isProduitsDerives" />
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <input
                                            type="checkbox"
                                            v-model="form.isBesoinSubvention"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            :disabled="isDisabled"
                                        />
                                        <InputLabel for="isBesoinSubvention" value="les dossiers de demande de subventions" class="text-sm font-medium" />
                                        <InputError class="mt-2" :message="form.errors.isBesoinSubvention" />
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <input
                                            type="checkbox"
                                            v-model="form.isBesoinPromo"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            :disabled="isDisabled"
                                        />
                                        <InputLabel for="isBesoinPromo" value="la promo" class="text-sm font-medium" />
                                        <InputError class="mt-2" :message="form.errors.isBesoinPromo" />
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <input
                                            type="checkbox"
                                            v-model="form.isBesoinDigitalMarketing"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            :disabled="isDisabled"
                                        />
                                        <InputLabel for="isBesoinDigitalMarketing" value="faire/recourir à du digital marketing" class="text-sm font-medium" />
                                        <InputError class="mt-2" :message="form.errors.isBesoinDigitalMarketing" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                                    <div class="flex items-center space-x-4">
                                        <input
                                            type="checkbox"
                                            v-model="form.isBesoinContacts"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            :disabled="isDisabled"
                                        />
                                        <InputLabel for="isBesoinContacts" value="mise en contact avec des partenaires (mix, mastering, vidéo, etc.)" class="text-sm font-medium" />
                                        <InputError class="mt-2" :message="form.errors.isBesoinContacts" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                                    <div v-if="form.isBesoinContacts">
                                        <InputLabel for="besoinContacts" value="Pourquoi ?" class="required text-sm font-medium" />
                                        <TextArea
                                            id="besoinContacts"
                                            type="text"
                                            class="mt-1 block w-full transition duration-150 ease-in-out"
                                            v-model="form.besoinContacts"
                                            rows="5"
                                            autocomplete="besoinContacts"
                                            required
                                            :disabled="isDisabled"

                                        />
                                        <InputError class="mt-2" :message="form.errors.besoinContacts" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-hidden bg-white shadow-lg rounded-lg dark:bg-gray-800">
                        <div class="p-8">
                            <div class="space-y-6 border-gray-700 text-gray-900 dark:text-gray-100 pb-6">
                                <h3 class="text-lg font-medium flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mr-4 size-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                </svg>
                                Informations financières</h3>
                                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                                    <div>
                                        <InputLabel for="artistIBAN" value="IBAN de l'artiste" class="required text-sm font-medium" />
                                        <TextInput
                                            id="artistIBAN"
                                            type="text"
                                            class="mt-1 block w-full transition duration-150 ease-in-out"
                                            v-model="form.artistIBAN"
                                            autocomplete="artistIBAN"
                                            required
                                            :disabled="isDisabled"
                                        />
                                        <InputError class="mt-2" :message="form.errors.artistIBAN" />
                                    </div>
                                    <div>
                                        <InputLabel for="budget" value="Budget pour le projet" class="required text-sm font-medium" />
                                        <TextInput
                                            id="budget"
                                            type="number"
                                            class="mt-1 block w-full transition duration-150 ease-in-out"
                                            v-model="form.budget"
                                            autocomplete="budget"
                                            required
                                            :disabled="isDisabled"
                                        />
                                        <InputError class="mt-2" :message="form.errors.budget" />
                                    </div>
                                    <div>
                                        <InputLabel for="sourceFinancement" value="Source(s) pour le financement du projet" class="required text-sm font-medium" />
                                        <TextArea
                                            id="sourceFinancement"
                                            type="text"
                                            class="mt-1 block w-full transition duration-150 ease-in-out"
                                            v-model="form.sourceFinancement"
                                            rows="5"
                                            autocomplete="sourceFinancement"
                                            required
                                            :disabled="isDisabled"
                                        />
                                        <InputError class="mt-2" :message="form.errors.sourceFinancement" />
                                    </div>
                                    <div>
                                        <InputLabel for="besoinFinancement" value="Besoin de soutien financier de la part du label ? Si oui,à quelle hauteur ?" class="required text-sm font-medium" />
                                        <TextArea
                                            id="besoinFinancement"
                                            type="text"
                                            class="mt-1 block w-full transition duration-150 ease-in-out"
                                            v-model="form.besoinFinancement"
                                            rows="5"
                                            autocomplete="besoinFinancement"
                                            required
                                            :disabled="isDisabled"
                                        />
                                        <InputError class="mt-2" :message="form.errors.besoinFinancement" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-hidden bg-white shadow-lg rounded-lg dark:bg-gray-800">
                        <div class="p-8">
                            <div class="space-y-6 border-gray-700 text-gray-900 dark:text-gray-100 pb-6">
                                <h3 class="text-lg font-medium flex items-center">
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
                                            :disabled="isDisabled"
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
                                            :disabled="isDisabled"
                                        />
                                        <InputError class="mt-2" :message="form.errors.envies" />
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-end pt-6">
                                <PrimaryButton
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing || isDisabled"
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