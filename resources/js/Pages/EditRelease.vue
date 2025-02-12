<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
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
    }
});

const form = useForm({
    catalog: props.release.catalog,
    name: props.release.name,
    artistName: props.release.artistName,
    artistIBAN: props.release.artistIBAN,
    artistBiography: props.release.artistBiography,
    description: props.release.description,

    release_type_id: props.release.release_type_id || null,
    release_format_ids: props.release.release_formats?.map(format => format.id) || [],

    tracks: props.release.release_tracks?.map(track => ({
        id: track.id,
        title: track.title,
        number: track.number,
        isSingle: Boolean(track.isSingle),
        hasClip: Boolean(track.hasClip)
    })) || [],

    members: props.release.release_members?.map(member => ({
        id: member.id,
        firstname: member.firstname,
        lastname: member.lastname,
    })) || [],
    
    releaseTypes: props.releaseTypes,
    releaseFormats: props.releaseFormats,
    releaseTracks: props.releaseTracks,
    releaseMembers: props.releaseMembers,
});

const addNewTrack = () => {
    const nextTrackNumber = form.tracks.length + 1;
    form.tracks.push({
        id: null,
        title: '',
        number: nextTrackNumber,
        isSingle: false,
        hasClip: false,
    });
};

const addNewMember = () => {
    form.members.push({
        id: null,
        firstname: '',
        lastname: '',
    });
};

const deleteTrack = (index) => {
    form.tracks.splice(index, 1);
};

const deleteMember = (index) => {
    form.members.splice(index, 1);
};

const submit = () => {
    if (form.tracks.some(track => !track.title) || form.tracks.some(track => !track.number )) {
        return;
    }
    if (form.members.some(member => !member.firstname) || form.members.some(member => !member.lastname)) {
        return;
    }
    form.put(route('update-release', props.release.id));
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Dashboard > Editer une release
            </h2>
        </template>

        <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <form @submit.prevent="submit" class="space-y-6">
        <div class="overflow-hidden bg-white shadow-lg rounded-lg dark:bg-gray-800">
            <div class="p-8">
                
                    <!-- Section Informations Principales -->
                    <div class="space-y-6 border-gray-700 pb-6">
                        <h3 class="text-xl font-bold text-gray-100">Informations sur l'artiste</h3>
                        <div class="">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 mb-6">
                            <div>
                                <InputLabel for="artistName" value="Nom d'artiste" class="text-sm font-medium" />
                                <TextInput
                                    id="artistName"
                                    type="text"
                                    class="mt-1 block w-full transition duration-150 ease-in-out"
                                    v-model="form.artistName"
                                    required
                                    autocomplete="artistName"
                                />
                                <InputError class="mt-2" :message="form.errors.artistName" />
                            </div>

                            <div>
                                <InputLabel for="artistIBAN" value="IBAN de l'artiste" class="text-sm font-medium" />
                                <TextInput
                                    id="artistIBAN"
                                    type="text"
                                    class="mt-1 block w-full transition duration-150 ease-in-out"
                                    v-model="form.artistIBAN"
                                    required
                                    autocomplete="artistIBAN"
                                />
                                <InputError class="mt-2" :message="form.errors.artistIBAN" />
                            </div>
                        </div>
                        <InputLabel for="artistBiography" value="Bio de l'artiste" class="text-sm font-medium" />
                        <TextArea
                            id="artistBiography"
                            type="text"
                            class="mt-1 block w-full transition duration-150 ease-in-out"
                            v-model="form.artistBiography"
                            rows="5"
                            required
                            autocomplete="artistBiography"
                        />
                        <InputError class="mt-2" :message="form.errors.artistBiography" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-hidden bg-white shadow-lg rounded-lg dark:bg-gray-800">
                <div class="p-8">
                    <div class="space-y-6 border-gray-700 pb-6">
                        <h3 class="text-lg font-medium text-gray-100">Informations sur les membres</h3>
                        <div class="grid grid-cols-1">
                            <div>
                                    <InputLabel value="Membres" class="text-sm font-medium" />
                                    <div v-for="(member, index) in form.members" :key="member.id || 'new'" class="relative">
                                        <div class="grid grid-cols-12 gap-2 mt-2 items-center">
                                            <TextInput
                                                type="text"
                                                v-model="member.firstname"
                                                class="col-span-5 block w-full transition duration-150 ease-in-out"
                                                placeholder="Prénom"
                                            />
                                            <TextInput
                                                type="text"
                                                v-model="member.lastname"
                                                class="col-span-6 block w-full transition duration-150 ease-in-out"
                                                placeholder="Nom"
                                            />
                                            <button
                                                v-if="index === form.members.length - 1"
                                                type="button" 
                                                @click="deleteMember(index)"
                                                class="col-span-1 h-9 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors flex items-center justify-center"
                                            >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <button 
                                            type="button" 
                                            @click="addNewMember"
                                            class="py-1 px-2.5 h-8 mt-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 transition-colors"
                                        >
                                        Ajouter un membre
                                        </button>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-hidden bg-white shadow-lg rounded-lg dark:bg-gray-800">
                <div class="p-8">

                    <!-- Section Types et Formats -->
                    <div class="space-y-6 border-gray-700 pb-6">
                        <h3 class="text-lg font-medium text-gray-100">Informations sur la sortie</h3>
                        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                            
                                    <div>
                                        <InputLabel for="catalog" value="N° de catalogue" class="text-sm font-medium" />
                                        <TextInput
                                            id="catalog"
                                            type="text"
                                            class="mt-1 block w-full transition duration-150 ease-in-out"
                                            v-model="form.catalog"
                                            required
                                            autocomplete="catalog"
                                        />
                                        <InputError class="" :message="form.errors.catalog" />
                                    </div>

                                    <div>
                                        <InputLabel for="name" value="Nom de la sortie" class="text-sm font-medium" />
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
                                    <InputLabel value="Formats de sortie" class="text-sm font-medium mb-1" />
                                    <div class="grid grid-cols-4 gap-2">
                                        <div v-for="format in props.releaseFormats" :key="format.id" 
                                            class="flex items-center p-2.5 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors">
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
                                    <InputLabel value="Type de sortie" class="text-sm font-medium mb-1" />
                                    <div class="grid grid-cols-3 gap-2">
                                        <div v-for="type in props.releaseTypes" :key="type.id" 
                                            class="flex items-center p-2.5 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors">
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
                        </div>
                        <div>
                            <InputLabel for="description" value="Description de la sortie" class="text-sm font-medium" />
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
                        <div class="">
                            <div>
                                    <InputLabel value="Tracklist" class="text-sm font-medium" />
                                    <div v-for="(track, index) in form.tracks" :key="track.id || 'new'" class="relative">
                                        <div class="flex mt-2 items-center">
                                            <TextInput
                                                type="text"
                                                v-model="track.number"
                                                class="w-12 transition duration-150 ease-in-out !bg-gray-800 !border-gray-800 !text-gray-100"
                                                disabled
                                            />
                                            <TextInput
                                                type="text"
                                                v-model="track.title"
                                                class="flex transition duration-150 ease-in-out"
                                                placeholder="Titre"
                                            />
                                            <div class="col-span-1 flex items-center p-2 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors">
                                                <input
                                                    type="checkbox"
                                                    v-model="track.isSingle"
                                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                />
                                                <label :for="'isSingle-' + index" class="ml-2 text-sm text-gray-100">
                                                    Single
                                                </label>
                                            </div>
                                            <div class="col-span-1 flex items-center p-2 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors">
                                                <input
                                                    type="checkbox"
                                                    v-model="track.hasClip"
                                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                />
                                                <label :for="'hasClip-' + index" class="ml-2 text-sm text-gray-100">
                                                    Clip
                                                </label>
                                            </div>
                                            <button
                                                v-if="index === form.tracks.length - 1"
                                                type="button" 
                                                @click="deleteTrack(index)"
                                                class="h-9 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors flex items-center justify-center"
                                            >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>

                                            </button>
                                        </div>
                                    </div>
                                    <button 
                                            type="button" 
                                            @click="addNewTrack"
                                            class="py-1 px-2.5 h-8 mt-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 transition-colors"
                                        >
                                        Ajouter une track
                                        </button>
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
