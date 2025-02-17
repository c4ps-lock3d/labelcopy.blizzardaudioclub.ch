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
    style: props.release.style,
    price: props.release.price,
    description: props.release.description,
    credits: props.release.credits,
    remerciements: props.release.remerciements,

    release_type_id: props.release.release_type_id || null,
    release_format_ids: props.release.release_formats?.map(format => format.id) || [],

    tracks: props.release.release_tracks?.length > 0 
        ? props.release.release_tracks.map(track => ({
            id: track.id,
            title: track.title,
            number: track.number,
            isSingle: Boolean(track.isSingle),
            hasClip: Boolean(track.hasClip)
        }))
        : [{
            id: null,
            title: '',
            number: 1,
            isSingle: false,
            hasClip: false
        }],

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
                        <h3 class="text-xl font-bold text-gray-100 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mr-4 size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            Informations sur l'artiste</h3>
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
                        <InputLabel for="artistBiography" value="Biographie de l’artiste " class="text-sm font-medium" />
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
                        <h3 class="text-xl font-bold text-gray-100 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mr-4 size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                            </svg>
                            Informations sur le(s) membre(s)</h3>
                            <InputLabel value="" class="text-sm font-medium" />
                                <table class="min-w-full rounded-md overflow-hidden border-gray-700 !mt-1">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="w-16 px-3 py-2.5 text-left text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap">Prénom</th>
                                            <th scope="col" class="w-full px-3 py-2.5 text-left text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap">Nom</th>
                                            <th scope="col" class="w-16 px-3 py-2.5 text-sm font-semibold bg-gray-700 text-gray-100">
                                                <button 
                                                        type="button" 
                                                        @click="addNewMember"
                                                        class="w-9 h-9 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 transition-colors flex items-center justify-center"
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
                                                    class="transition duration-150 ease-in-out"
                                                    placeholder="Titre"
                                                />
                                            </td>
                                            <td class="px-1.5 py-2">
                                                <TextInput
                                                    type="text"
                                                    v-model="member.lastname"
                                                    class="w-full transition duration-150 ease-in-out"
                                                    placeholder="Titre"
                                                />
                                            </td>
                                            <td class="whitespace-nowrap px-3 py-2">
                                                <button
                                                    v-if="form.members.length > 1 && index === form.members.length - 1"
                                                    type="button" 
                                                    @click="deleteMember(index)"
                                                    class="w-9 h-9 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors flex items-center justify-center"
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
                                        <InputLabel for="style" value="Style musical" class="text-sm font-medium" />
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
                                    <InputLabel value="Format(s)" class="text-sm font-medium mb-1" />
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
                                    <InputLabel value="Type" class="text-sm font-medium mb-1" />
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
                                <div>
                                    <InputLabel for="price" value="Prix" class="text-sm font-medium" />
                                    <TextInput
                                        id="price"
                                        type="text"
                                        class="mt-1 block w-full transition duration-150 ease-in-out"
                                        v-model="form.price"
                                        required
                                        autocomplete="price"
                                    />
                                    <InputError class="" :message="form.errors.price" />
                                </div>
                            </div>
                                <div class="pt-3">
                                    <InputLabel for="description" value="Description" class="text-sm font-medium" />
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
                                <table class="min-w-full rounded-md overflow-hidden border-gray-700 !mt-1">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="w-8 px-3 py-2.5 text-left text-sm font-semibold text-gray-100 bg-gray-700">#</th>
                                            <th scope="col" class="w-full px-3 py-2.5 text-left text-sm font-semibold bg-gray-700 text-gray-100">Titre</th>
                                            <th scope="col" class="w-16 px-3 py-2.5 text-center text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap">Single ?</th>
                                            <th scope="col" class="w-16 px-3 py-2.5 text-center text-sm font-semibold bg-gray-700 text-gray-100 whitespace-nowrap">Vidéoclip ?</th>
                                            <th scope="col" class="w-16 px-3 py-2.5 text-center text-sm font-semibold bg-gray-700 text-gray-100">
                                                <button 
                                                        type="button" 
                                                        @click="addNewTrack"
                                                        class="w-9 h-9 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700 transition-colors flex items-center justify-center"
                                                    >
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                    </svg>
                                                </button>
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
                                                    class="w-full transition duration-150 ease-in-out"
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
                                                <td class="whitespace-nowrap px-3 py-2">
                                                    <button
                                                        v-if="form.tracks.length > 1 && index === form.tracks.length - 1"
                                                        type="button" 
                                                        @click="deleteTrack(index)"
                                                        class="w-9 h-9 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-colors flex items-center justify-center"
                                                    >
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                                        <div>
                                            <InputLabel for="credits" value="Crédits" class="text-sm font-medium" />
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
                                                required
                                                autocomplete="remerciements"
                                            />
                                            <InputError class="mt-2" :message="form.errors.remerciements" />
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