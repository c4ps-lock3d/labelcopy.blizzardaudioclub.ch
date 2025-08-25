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
});

const form = useForm({
    catalog: props.release.catalog,
    name: props.release.name,
    artistName: props.release.artistName,
    facebook: props.release.facebook,
    instagram: props.release.instagram,
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
    
    tracks: props.release.release_tracks?.length > 0 
        ? props.release.release_tracks.map(track => ({
            id: track.id,
            title: track.title,
            number: track.number,
            isSingle: Boolean(track.isSingle),
            hasClip: Boolean(track.hasClip),
            IRSC: track.IRSC || '',
            release_date_single: track.release_date_single || '',
            release_description: track.release_description || '',
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
            release_description: '',
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
        shirtsize: member.shirtsize || '',
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
            shirtsize: '',
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

    //file_release: null,
    //file_cover: null,
    _method: 'PUT'
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

const deleteTrack = (index) => {
    form.tracks.splice(index, 1);
};

const deleteMember = (index) => {
    form.members.splice(index, 1);
};

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

    form.post(route('update-release', props.release.id), {
        preserveScroll: true,
        forceFormData: true
    });
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
                        <div class="grid grid-cols-1 gap-8 md:content-stretch content-stretch md:grid-cols-2">
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
                                    rows="13"
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
                                        <div class="relative flex items-center w-full">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="absolute left-3 size-5 text-gray-400 dark:text-gray-600">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" />
                                            </svg>
                                            <TextInput
                                                id="artistWebsite"
                                                type="text"
                                                class="pl-10 w-full transition duration-150 ease-in-out"
                                                v-model="form.artistWebsite"
                                                autocomplete="artistWebsite"
                                                placeholder="Site web"
                                                :disabled="isDisabled"
                                            />
                                        </div>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.artistWebsite" />
                                    <div class="flex items-center gap-4">
                                        <div class="relative flex items-center w-full">
                                            <svg class="absolute left-3 size-5 text-gray-400 dark:text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                <path fill="currentColor" d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1v2h3v3h-3v6.95c5.05-.5 9-4.76 9-9.95"/>
                                            </svg>
                                            <TextInput
                                                id="facebook"
                                                type="text"
                                                class="pl-10 w-full transition duration-150 ease-in-out"
                                                v-model="form.facebook"
                                                autocomplete="facebook"
                                                placeholder="Facebook"
                                                :disabled="isDisabled"
                                            />
                                        </div>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.facebook" />
                                    <div class="flex items-center gap-4">
                                        <div class="relative flex items-center w-full">
                                            <svg class="absolute left-3 size-5 text-gray-400 dark:text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                <path fill="currentColor" d="M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6m0 8a5 5 0 1 1 5-5 5 5 0 0 1-5 5m5-13.5a1.5 1.5 0 1 0 1.5 1.5 1.5 1.5 0 0 0-1.5-1.5M20.5 2h-17A1.5 1.5 0 0 0 2 3.5v17A1.5 1.5 0 0 0 3.5 22h17a1.5 1.5 0 0 0 1.5-1.5v-17A1.5 1.5 0 0 0 20.5 2M4 19V5h16v14z"/>
                                            </svg>
                                            <TextInput
                                                id="instagram"
                                                type="text"
                                                class="pl-10 w-full transition duration-150 ease-in-out"
                                                v-model="form.instagram"
                                                placeholder="Instagram"
                                                :disabled="isDisabled"
                                            />
                                        </div>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.instagram" />
                                    <div class="flex items-center gap-4">
                                        <div class="relative flex items-center w-full">
                                            <svg class="absolute left-3 size-5 text-gray-400 dark:text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                <path fill="currentColor" d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0C.488 3.45.029 5.804 0 12c.029 6.185.484 8.549 4.385 8.816c3.6.245 11.626.246 15.23 0C23.512 20.55 23.971 18.196 24 12c-.029-6.185-.484-8.549-4.385-8.816M9 16V8l8 3.993L9 16"/>
                                            </svg>
                                            <TextInput
                                                id="youtube"
                                                type="text"
                                                class="pl-10 w-full transition duration-150 ease-in-out"
                                                v-model="form.youtube"
                                                placeholder="YouTube"
                                                :disabled="isDisabled"
                                            />
                                        </div>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.youtube" />
                                    <div class="flex items-center gap-4">
                                        <div class="relative flex items-center w-full">
                                            <svg class="absolute left-3 size-5 text-gray-400 dark:text-gray-600" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
                                                width="800px" height="800px" viewBox="0 0 97.75 97.75" xml:space="preserve"
                                                >
                                            <g>
                                                <path fill="currentColor" d="M48.875,0C21.882,0,0,21.882,0,48.875S21.882,97.75,48.875,97.75S97.75,75.868,97.75,48.875S75.868,0,48.875,0z
                                                    M64.835,70.857H12.593l20.32-43.965h52.244L64.835,70.857z"/>
                                            </g>
                                            </svg>
                                            <TextInput
                                                id="bandcamp"
                                                type="text"
                                                class="pl-10 w-full transition duration-150 ease-in-out"
                                                v-model="form.bandcamp"
                                                placeholder="Bandcamp"
                                                :disabled="isDisabled"
                                            />
                                        </div>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.bandcamp" />
                                    <div class="flex items-center gap-4">
                                        <div class="relative flex items-center w-full">
                                            <svg class="absolute left-3 size-5 text-gray-400 dark:text-gray-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                <path fill="currentColor" d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12s12-5.4 12-12S18.66 0 12 0m5.521 17.34c-.24.359-.66.48-1.021.24c-2.82-1.74-6.36-2.101-10.561-1.141c-.418.122-.779-.179-.899-.539c-.12-.421.18-.78.54-.9c4.56-1.021 8.52-.6 11.64 1.32c.36.18.48.659.301 1.02m1.44-3.3c-.301.42-.841.6-1.262.3c-3.239-1.98-8.159-2.58-11.939-1.38c-.479.12-1.02-.12-1.14-.6c-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2m.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.381-.721c-.18-.601.18-1.2.72-1.381c4.26-1.26 11.28-1.02 15.721 1.621c.539.3.719 1.02.419 1.56c-.299.421-1.02.599-1.559.3"/>
                                            </svg>
                                            <TextInput
                                                id="spotify"
                                                type="text"
                                                class="pl-10 w-full transition duration-150 ease-in-out"
                                                v-model="form.spotify"
                                                placeholder="Spotify"
                                                :disabled="isDisabled"
                                            />
                                        </div>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.applemusic" />
                                    <div class="flex items-center gap-4">
                                        <div class="relative flex items-center w-full">
                                            <svg class="absolute left-3 size-5 text-gray-400 dark:text-gray-600" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path fill="currentColor" d="m24 6.124c0-.029.001-.063.001-.097 0-.743-.088-1.465-.253-2.156l.013.063c-.312-1.291-1.1-2.359-2.163-3.031l-.02-.012c-.536-.35-1.168-.604-1.847-.723l-.03-.004c-.463-.084-1.003-.138-1.553-.15h-.011c-.04 0-.083-.01-.124-.013h-12.025c-.152.01-.3.017-.455.026-.791.016-1.542.161-2.242.415l.049-.015c-1.306.501-2.327 1.495-2.853 2.748l-.012.033c-.17.409-.297.885-.36 1.38l-.003.028c-.051.343-.087.751-.1 1.165v.016c0 .032-.007.062-.01.093v12.224c.01.14.017.283.027.424.02.861.202 1.673.516 2.416l-.016-.043c.609 1.364 1.774 2.387 3.199 2.792l.035.009c.377.111.817.192 1.271.227l.022.001c.555.053 1.11.06 1.667.06h11.028c.554 0 1.099-.037 1.633-.107l-.063.007c.864-.096 1.645-.385 2.321-.823l-.021.013c.825-.539 1.47-1.29 1.867-2.176l.013-.032c.166-.383.295-.829.366-1.293l.004-.031c.084-.539.132-1.161.132-1.794 0-.086-.001-.171-.003-.256v.013q0-5.7 0-11.394zm-6.424 3.99v5.712c.001.025.001.054.001.083 0 .407-.09.794-.252 1.14l.007-.017c-.273.562-.771.979-1.373 1.137l-.015.003c-.316.094-.682.156-1.06.173h-.01c-.029.002-.062.002-.096.002-1.033 0-1.871-.838-1.871-1.871 0-.741.431-1.382 1.056-1.685l.011-.005c.293-.14.635-.252.991-.32l.027-.004c.378-.082.758-.153 1.134-.24.264-.045.468-.252.51-.513v-.003c.013-.057.02-.122.02-.189 0-.002 0-.003 0-.005q0-2.723 0-5.443c-.001-.066-.01-.13-.027-.19l.001.005c-.026-.134-.143-.235-.283-.235-.006 0-.012 0-.018.001h.001c-.178.013-.34.036-.499.07l.024-.004q-1.14.225-2.28.456l-3.7.748c-.016 0-.032.01-.048.013-.222.03-.392.219-.392.447 0 .015.001.03.002.045v-.002.13q0 3.9 0 7.801c.001.028.001.062.001.095 0 .408-.079.797-.224 1.152l.007-.021c-.264.614-.792 1.072-1.436 1.235l-.015.003c-.319.096-.687.158-1.067.172h-.008c-.031.002-.067.003-.104.003-.913 0-1.67-.665-1.815-1.536l-.001-.011c-.02-.102-.031-.218-.031-.338 0-.785.485-1.458 1.172-1.733l.013-.004c.315-.127.687-.234 1.072-.305l.036-.005c.287-.06.575-.116.86-.177.341-.05.6-.341.6-.693 0-.007 0-.015 0-.022v.001-.15q0-4.44 0-8.883c0-.002 0-.004 0-.007 0-.129.015-.254.044-.374l-.002.011c.066-.264.277-.466.542-.517l.004-.001c.255-.066.515-.112.774-.165.733-.15 1.466-.3 2.2-.444l2.27-.46c.67-.134 1.34-.27 2.01-.4.181-.042.407-.079.637-.104l.027-.002c.018-.002.04-.004.061-.004.27 0 .489.217.493.485.008.067.012.144.012.222v.001q0 2.865 0 5.732z"/>
                                            </svg>
                                            <TextInput
                                                id="applemusic"
                                                type="text"
                                                class="pl-10 w-full transition duration-150 ease-in-out"
                                                v-model="form.applemusic"
                                                placeholder="Apple Music"
                                                :disabled="isDisabled"
                                            />
                                        </div>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.spotify" />
                                    <div class="flex items-center gap-4">
                                        <div class="relative flex items-center w-full">
                                            <svg  class="absolute left-3 size-5 text-gray-400 dark:text-gray-600" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-271 345.8 256 111.2" xml:space="preserve">
                                            <g>
                                                <path fill="currentColor" d="M-238.4,398.1c-0.8,0-1.4,0.6-1.5,1.5l-2.3,28l2.3,27.1c0.1,0.8,0.7,1.5,1.5,1.5c0.8,0,1.4-0.6,1.5-1.5l2.6-27.1l-2.6-28
                                                    C-237,398.7-237.7,398.1-238.4,398.1z"/>
                                                <path fill="currentColor" d="M-228.2,399.9c-0.9,0-1.7,0.7-1.7,1.7l-2.1,26l2.1,27.3c0.1,1,0.8,1.7,1.7,1.7c0.9,0,1.6-0.7,1.7-1.7l2.4-27.3l-2.4-26
                                                    C-226.6,400.6-227.3,399.9-228.2,399.9z"/>
                                                <path fill="currentColor" d="M-258.6,403.5c-0.5,0-1,0.4-1.1,1l-2.5,23l2.5,22.5c0.1,0.6,0.5,1,1.1,1c0.5,0,1-0.4,1.1-1l2.9-22.5l-2.9-23
                                                    C-257.7,404-258.1,403.5-258.6,403.5z"/>
                                                <path fill="currentColor" d="M-268.1,412.3c-0.5,0-1,0.4-1,1l-1.9,14.3l1.9,14c0.1,0.6,0.5,1,1,1s0.9-0.4,1-1l2.2-14l-2.2-14.2
                                                    C-267.2,412.8-267.6,412.3-268.1,412.3z"/>
                                                <path fill="currentColor" d="M-207.5,373.5c-1.2,0-2.1,0.9-2.2,2.1l-1.9,52l1.9,27.2c0.1,1.2,1,2.1,2.2,2.1s2.1-0.9,2.2-2.1l2.1-27.2l-2.1-52
                                                    C-205.4,374.4-206.4,373.5-207.5,373.5z"/>
                                                <path fill="currentColor" d="M-248.6,399c-0.7,0-1.2,0.5-1.3,1.3l-2.4,27.3l2.4,26.3c0.1,0.7,0.6,1.3,1.3,1.3c0.7,0,1.2-0.5,1.3-1.2l2.7-26.3l-2.7-27.3
                                                    C-247.4,399.6-247.9,399-248.6,399z"/>
                                                <path fill="currentColor" d="M-217.9,383.4c-1,0-1.9,0.8-1.9,1.9l-2,42.3l2,27.3c0.1,1.1,0.9,1.9,1.9,1.9s1.9-0.8,1.9-1.9l2.3-27.3l-2.3-42.3
                                                    C-216,384.2-216.9,383.4-217.9,383.4z"/>
                                                <path fill="currentColor" d="M-154.4,359.3c-1.8,0-3.2,1.4-3.2,3.2l-1.2,65l1.2,26.1c0,1.8,1.5,3.2,3.2,3.2c1.8,0,3.2-1.5,3.2-3.2l1.4-26.1l-1.4-65
                                                    C-151.1,360.8-152.6,359.3-154.4,359.3z"/>
                                                <path fill="currentColor" d="M-197.1,368.9c-1.3,0-2.3,1-2.4,2.4l-1.8,56.3l1.8,26.9c0,1.3,1.1,2.3,2.4,2.3s2.3-1,2.4-2.4l2-26.9l-2-56.3
                                                    C-194.7,370-195.8,368.9-197.1,368.9z"/>
                                                <path fill="currentColor" d="M-46.5,394c-4.3,0-8.4,0.9-12.2,2.4C-61.2,368-85,345.8-114,345.8c-7.1,0-14,1.4-20.1,3.8c-2.4,0.9-3,1.9-3,3.7v99.9
                                                    c0,1.9,1.5,3.5,3.4,3.7c0.1,0,86.7,0,87.3,0c17.4,0,31.5-14.1,31.5-31.5C-15,408.1-29.1,394-46.5,394z"/>
                                                <path fill="currentColor" d="M-143.6,353.2c-1.9,0-3.4,1.6-3.5,3.5l-1.4,70.9l1.4,25.7c0,1.9,1.6,3.4,3.5,3.4c1.9,0,3.4-1.6,3.5-3.5l1.5-25.8l-1.5-70.9
                                                    C-140.2,354.8-141.7,353.2-143.6,353.2z"/>
                                                <path fill="currentColor" d="M-186.5,366.8c-1.4,0-2.5,1.1-2.6,2.6l-1.6,58.2l1.6,26.7c0,1.4,1.2,2.6,2.6,2.6s2.5-1.1,2.6-2.6l1.8-26.7l-1.8-58.2
                                                    C-184,367.9-185.1,366.8-186.5,366.8z"/>
                                                <path fill="currentColor" d="M-175.9,368.1c-1.5,0-2.8,1.2-2.8,2.8l-1.5,56.7l1.5,26.5c0,1.6,1.3,2.8,2.8,2.8s2.8-1.2,2.8-2.8l1.7-26.5l-1.7-56.7
                                                    C-173.1,369.3-174.3,368.1-175.9,368.1z"/>
                                                <path fill="currentColor" d="M-165.2,369.9c-1.7,0-3,1.3-3,3l-1.4,54.7l1.4,26.3c0,1.7,1.4,3,3,3c1.7,0,3-1.3,3-3l1.5-26.3l-1.5-54.7
                                                    C-162.2,371.3-163.5,369.9-165.2,369.9z"/>
                                            </g>
                                            </svg>
                                            <TextInput
                                                id="soundcloud"
                                                type="text"
                                                class="pl-10 w-full transition duration-150 ease-in-out"
                                                v-model="form.soundcloud"
                                                placeholder="SoundCloud"
                                                :disabled="isDisabled"
                                            />
                                        </div>
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
                        <InputLabel value="Liste des membres (tous les ayant-droits de l'œuvre)" class="required text-sm font-medium" />
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 !mt-1 items-stretch">
                            <div
                                v-for="(member, index) in form.members"
                                :key="member.id || 'new-' + index"
                                class="bg-white dark:bg-gray-700 rounded-lg shadow p-4 flex flex-col gap-3 border border-gray-200 dark:border-gray-600 relative h-full"
                            >
                                <div class="flex items-center justify-between mb-2 min-h-[2rem]">
                                <span class="font-semibold text-indigo-700 dark:text-indigo-300">
                                    {{ member.is_reference ? 'Membre de référence' : `Membre ${index + 1}` }}
                                </span>
                                <button
                                    v-if="form.members.length > 1 && !member.is_reference"
                                    type="button"
                                    @click="deleteMember(index)"
                                    class="w-8 h-8 bg-red-500/10 border border-red-500/20 rounded-md text-red-400 transition-colors flex items-center justify-center"
                                    :disabled="isDisabled"
                                    title="Supprimer ce membre"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                            <div class="grid grid-cols-2 gap-x-3 gap-y-2">
                                <InputLabel for="firstname" value="Prénom" class="text-sm flex items-center" />
                                <TextInput
                                    id="firstname"
                                    type="text"
                                    v-model="member.firstname"
                                    :class="{
                                        'block transition duration-150 ease-in-out w-full': true
                                    }"
                                    required
                                    :disabled="member.is_reference || isDisabled"
                                />

                                <InputLabel for="lastname" value="Nom" class="text-sm flex items-center" />
                                <TextInput
                                    id="lastname"
                                    type="text"
                                    v-model="member.lastname"
                                    :class="{
                                        'block transition duration-150 ease-in-out w-full': true
                                    }"
                                    required
                                    :disabled="member.is_reference || isDisabled"
                                />

                                <InputLabel for="birth_date" value="Date de naissance" class="text-sm flex items-center" />
                                <TextInput
                                    id="birth_date"
                                    type="date"
                                    v-model="member.birth_date"
                                    class="transition duration-150 ease-in-out w-full"
                                    required
                                    :disabled="isDisabled"
                                />

                                <InputLabel for="shirtsize" value="Taille t-shirt" class="text-sm flex items-center" />
                                <select
                                    id="shirtsize"
                                    v-model="member.shirtsize"
                                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 text-gray-900 dark:focus:border-indigo-600 dark:focus:ring-indigo-600 w-full transition duration-150 ease-in-out"
                                    required
                                    :disabled="isDisabled"
                                >
                                    <option value="" disabled>Choisir une taille</option>
                                    <option value="S">S</option>
                                    <option value="M">M</option>
                                    <option value="L">L</option>
                                    <option value="XL">XL</option>
                                </select>

                                <InputLabel for="IPI" value="N° IPI (SUISA)" class="text-sm flex items-center" />
                                <TextInput
                                    id="IPI"
                                    type="text"
                                    v-model="member.IPI"
                                    class="w-full transition duration-150 ease-in-out"
                                    required
                                    :disabled="isDisabled"
                                />
                            </div>
                            </div>
                            <!-- Carte d'ajout -->
                            <button
                                v-if="form.members.length < 12"
                                type="button"
                                @click="addNewMember"
                                class="flex flex-col items-center justify-center border-2 border-dashed border-indigo-400 rounded-lg bg-indigo-50 dark:bg-gray-900 text-indigo-600 hover:bg-indigo-100 transition-colors h-full"
                                :disabled="isDisabled"
                                style="min-height:11rem"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 mb-2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <span class="font-medium">Ajouter un membre</span>
                            </button>
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
                                    <InputLabel :for="'barcode-' + formatId" :value="`Code-barres ${props.releaseFormats.find(f => f.id === formatId)?.name}`" class="text-sm font-medium" />
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
                                    <InputLabel :for="'price-' + formatId" :value="`Prix ${props.releaseFormats.find(f => f.id === formatId)?.name}`" class="text-sm font-medium mt-2" />
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
                                <InputLabel value="Liste des titres" class="required text-sm font-medium" />
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 !mt-1 items-stretch">
                                    <div
                                        v-for="(track, index) in form.tracks"
                                        :key="track.id || 'new-' + index"
                                        class="bg-white dark:bg-gray-700 rounded-lg shadow p-4 flex flex-col gap-3 border border-gray-200 dark:border-gray-600 relative h-full"
                                    >
                                        <div class="flex items-center justify-between mb-2 min-h-[2.4rem]">
                                            <span class="font-semibold text-indigo-700 dark:text-indigo-300">Titre {{ track.number }}</span>
                                            <span>
                                                <button
                                                    v-if="form.tracks.length > 1 && index === form.tracks.length - 1"
                                                    type="button"
                                                    @click="deleteTrack(index)"
                                                    class="w-8 h-8 bg-red-500/10 border border-red-500/20 rounded-md text-red-400 transition-colors flex items-center justify-center"
                                                    :disabled="isDisabled"
                                                    title="Supprimer ce titre"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                </button>
                                                <!-- Espace réservé pour garder la hauteur -->
                                                <span v-else class="inline-block w-8 h-8"></span>
                                            </span>
                                        </div>
                                        <TextInput
                                            type="text"
                                            v-model="track.title"
                                            class="w-full transition duration-150 ease-in-out"
                                            placeholder="Titre"
                                            required
                                            :disabled="isDisabled"
                                        />
                                        <div v-if="props.auth.user.name === 'lynxadmin'">
                                            <TextInput
                                                type="text"
                                                v-model="track.IRSC"
                                                class="w-full transition duration-150 ease-in-out"
                                                placeholder="ISRC"
                                            />
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <label class="flex items-center gap-1 text-sm">
                                                <input
                                                    type="checkbox"
                                                    v-model="track.hasClip"
                                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                    :disabled="isDisabled"
                                                />
                                                Vidéoclip ?
                                            </label>
                                            <label class="flex items-center gap-1 text-sm">
                                                <input
                                                    type="checkbox"
                                                    v-model="track.isSingle"
                                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                    :disabled="isDisabled"
                                                />
                                                Single ?
                                            </label>
                                        </div>
                                        <!-- Pourcentages par membre -->
                                        <div class="flex flex-col gap-2 mt-2">
                                            <div
                                                v-for="(participation, pIndex) in track.participations"
                                                :key="participation.member_id || 'new-' + pIndex"
                                                class="flex items-center gap-2"
                                            >
                                                <span class="text-xs text-gray-500 dark:text-gray-400 w-32 truncate">
                                                    {{ participation.firstname && participation.lastname
                                                        ? `${participation.firstname} ${participation.lastname}`
                                                        : 'Membre'
                                                    }}
                                                </span>
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
                                                <span class="text-xs text-gray-400">%</span>
                                            </div>
                                        </div>
                                        <div v-if="track.isSingle" class="flex flex-col gap-2">
                                            <TextInput
                                                type="date"
                                                v-model="track.release_date_single"
                                                class="transition duration-150 ease-in-out"
                                                :disabled="isDisabled"
                                            />
                                            <TextInput
                                                type="text"
                                                v-model="track.release_description"
                                                class="transition duration-150 ease-in-out"
                                                placeholder="Description"
                                                :disabled="isDisabled"
                                            />
                                        </div>
                                    </div>
                                    <!-- Carte d'ajout -->
                                    <button
                                        v-if="form.tracks.length < 20"
                                        type="button"
                                        @click="addNewTrack"
                                        class="flex flex-col items-center justify-center border-2 border-dashed border-indigo-400 rounded-lg bg-indigo-50 dark:bg-gray-900 text-indigo-600 hover:bg-indigo-100 transition-colors h-full"
                                        :disabled="isDisabled"
                                        style="min-height:11rem"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 mb-2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                        <span class="font-medium">Ajouter un titre</span>
                                    </button>
                                </div>
                                <!-- Deuxième tableau : Clé de répartition -->
                                <!-- <InputLabel value="Clé de répartition précise des membres/morceaux" class="required text-sm font-medium mt-8" />
                                <div class="overflow-x-auto rounded-md border border-gray-300 dark:border-gray-600 !mt-1">
                                    <table class="min-w-full">
                                        <thead>
                                            <tr class="bg-gray-100 dark:bg-gray-700">
                                                <td scope="col" class="w-8 px-3 py-2.5 text-left text-sm font-semibold text-gray-800 dark:text-gray-100">
                                                    #
                                                </td>
                                                <td v-if="form.members.length < 9" class="w-96 px-3 py-2 text-gray-900 dark:text-gray-100">
                                                    Titre
                                                </td>
                                                <template v-for="member in form.members" :key="member.id">
                                                    <th scope="col" class="required px-1 py-2.5 text-left text-sm font-semibold text-gray-800 dark:text-gray-100 whitespace-nowrap">
                                                        {{ member.firstname && member.lastname 
                                                            ? `${member.firstname.slice(0, 1)}. ${member.lastname.slice(0, 3)}` 
                                                            : 'Non défini' 
                                                        }}
                                                    </th>
                                                </template>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600 ">
                                            <tr v-for="(track, index) in form.tracks" :key="track.id" class="bg-white dark:bg-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                                
                                                    <td class="w-8 px-3 py-2 text-gray-900 dark:text-gray-100">
                                                        {{ track.number }}
                                                    </td>
                                                    <td v-if="form.members.length < 9" class="w-96 px-3 py-2 text-gray-900 dark:text-gray-100">
                                                        {{ track.title }}
                                                    </td>
                                               
                                                <template v-for="participation in track.participations" :key="participation.member_id">
                                                    <td class="px-1 py-2">
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
                                                </template>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div> -->
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
                                    <div class="flex items-center space-x-4 mt-2">
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