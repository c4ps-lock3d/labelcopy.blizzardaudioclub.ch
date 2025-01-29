<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
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
    }
});

const form = useForm({
    catalog: props.release.catalog,
    name: props.release.name,
    artistName: props.release.artistName,
    artistIBAN: props.release.artistIBAN,

    release_type_id: props.release.release_type_id || null,
    release_format_ids: props.release.release_formats?.map(format => format.id) || [],

    tracks: props.release.release_tracks?.map(track => ({
        id: track.id,
        title: track.title,
        number: track.number
    })) || [],
    
    releaseTypes: props.releaseTypes,
    releaseFormats: props.releaseFormats,
    releaseTracks: props.releaseTracks,
});

const addNewTrack = () => {
    form.tracks.push({
        id: null,
        title: ''
    });
};

const submit = () => {
    if (form.tracks.some(track => !track.title) || form.tracks.some(track => !track.number)) {
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
        <div class="overflow-hidden bg-white shadow-lg rounded-lg dark:bg-gray-800">
            <div class="p-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Section Informations Principales -->
                    <div class="space-y-6 border-b border-gray-700 pb-6">
                        <h3 class="text-lg font-medium text-gray-100">Informations sur l'artiste</h3>
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
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
                    </div>

                    <!-- Section Types et Formats -->
                    <h3 class="text-lg font-medium text-gray-100">Informations sur la sortie</h3>
                    <div class="grid grid-cols-2 gap-8">
                        <div class="space-y-6">
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
                                    <InputError class="mt-2" :message="form.errors.catalog" />
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
                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>
                           

                                <div>
                                    <InputLabel value="Type de sortie" class="text-sm font-medium mb-3" />
                                    <div class="grid grid-cols-3 gap-2">
                                        <div v-for="type in props.releaseTypes" :key="type.id" 
                                            class="flex items-center p-3 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors">
                                            <input
                                                type="radio"
                                                :id="'type-' + type.id"
                                                :value="type.id"
                                                v-model="form.release_type_id"
                                                class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                            <label :for="'type-' + type.id" class="ml-2 text-sm text-gray-100">
                                                {{ type.name }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <InputLabel value="Formats de sortie" class="text-sm font-medium mb-3" />
                                    <div class="grid grid-cols-4 gap-2">
                                        <div v-for="format in props.releaseFormats" :key="format.id" 
                                            class="flex items-center p-3 bg-gray-700 rounded-lg hover:bg-gray-600 transition-colors">
                                            <input
                                                type="checkbox"
                                                :id="'format-' + format.id"
                                                :value="format.id"
                                                v-model="form.release_format_ids"
                                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                            <label :for="'format-' + format.id" class="ml-2 text-sm text-gray-100">
                                                {{ format.name }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                        </div>
                        <div class="space-y-6">
                            <div>
                                <InputLabel value="Tracklist" class="text-sm font-medium" />
                                <div class="grid grid-cols-1">
                                    <div v-for="track in form.tracks" :key="track.id || 'new'" class="relative">
                                        <div class="grid grid-cols-12 gap-2">
                                        
                                        <TextInput
                                            type="text"
                                            v-model="track.number"
                                            class="col-span-2 mt-1 block w-full transition duration-150 ease-in-out"
                                            placeholder="#"
                                        />
                              
                                        <TextInput
                                            type="text"
                                            v-model="track.title"
                                            class="col-span-10 mt-1 block w-full transition duration-150 ease-in-out"
                                            placeholder="Titre"
                                        />
                                    </div>
                                    </div>
                                </div>
                                <div class="flex justify-end mt-4">
                                    <button 
                                        type="button" 
                                        @click="addNewTrack"
                                        class="px-2 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors"
                                    >
                                        Ajouter une track
                                    </button>
                                </div>
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
                </form>
            </div>
        </div>
    </div>
        </div>
    </AuthenticatedLayout>
</template>
