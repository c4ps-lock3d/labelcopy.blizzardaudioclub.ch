<script setup lang="ts">
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import InputError from '@/Components/InputError.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import { Head, useForm, Link } from '@inertiajs/vue3';
    import { computed, onMounted } from 'vue';
    //import Dropzone from 'dropzone';
    //import 'dropzone/dist/dropzone.css';

    const props = defineProps({
        auth: {
            type: Object,
            required: true,
        },
        release: {
            type: Object,
            required: true
        },
    });

    const form = useForm({
        catalog: props.release.catalog,
        isActive: Boolean(props.release.isActive),
        file_release: null,
        file_cover: null,
        
        _method: 'PUT'
    });

    // Constante de désactivation d'input, si le label désactive le formulaire de l'artiste.
    const isDisabled = computed(() => {
        return !props.release.isActive && props.auth.user.name !== 'lynxadmin';
    });

    interface UploadResponse {
    status: 'complete' | 'error';
    message?: string;
}

    onMounted(() => {
        const dropzone = new Dropzone("#my-dropzone", {
            url: route('upload-release', props.release.id),
            paramName: 'file_release',
            chunking: true,
            forceChunking: true,
            parallelUploads: 1,
            chunkSize: 1000000,
            maxFilesize: 10,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            init: function() {
            this.on("sending", function(file, xhr, formData) {
                formData.append("file_release", file);
            });
            this.on("success", function(file, responseData) {
                const response = responseData as UploadResponse;
                if (response.status === 'complete') {
                    alert("Fichier uploadé avec succès !");
                } else {
                    alert(response.message || "Une erreur s'est produite");
                }
            });
            this.on("error", function(file, errorMessage) {
                console.error('Erreur upload:', errorMessage);
                alert("Erreur lors de l'upload du fichier");
            });
        }
        });
    });

    const submit = () => {
        form.post(route('upload-release', props.release.id));
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
                <form action="{{ route('upload-release') }}" method="post" class="dropzone" id="my-dropzone">
                    <!-- <div class="bg-white shadow-lg rounded-lg dark:bg-gray-800">
                        <div class="p-6">
                            <div class="space-y-6 border-gray-700 text-gray-900 dark:text-gray-100 pb-6">
                                <h3 class="text-lg font-medium flex items-center mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mr-4 size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25H7.5a2.25 2.25 0 0 0-2.25 2.25v9a2.25 2.25 0 0 0 2.25 2.25h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25H15m0-3-3-3m0 0-3 3m3-3V15" />
                                    </svg>
                                    Transférer des fichiers
                                </h3>
                                <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                                    <div>
                                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="file_release">Masters audio</label>
                                        <div class="relative">
                                        <input 
                                            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-indigo-700 file:text-gray-100 hover:file:bg-indigo-800 dark:hover:file:bg-indigo-800 dark:file:bg-indigo-700 dark:file:text-gray-100" 
                                            aria-describedby="file_input_help" 
                                            id="file_release" 
                                            type="file"
                                            @change="form.file_release = ($event.target as HTMLInputElement).files[0]"
                                            accept=".zip"
                                            :disabled="isDisabled"
                                        >
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-300" id="file_input_help">Format accepté : ZIP</p>
                                        <InputError class="mt-2" :message="form.errors.file_release" />
                                    </div>

                                    <div>
                                        <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="file_cover">Pochette(s)</label>
                                        <div class="relative">
                                        <input 
                                            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-indigo-700 file:text-gray-100 hover:file:bg-indigo-800 dark:hover:file:bg-indigo-800 dark:file:bg-indigo-700 dark:file:text-gray-100" 
                                            aria-describedby="file_input_help" 
                                            id="file_cover" 
                                            type="file"
                                            @change="form.file_cover = ($event.target as HTMLInputElement).files[0]"
                                            accept=".zip"
                                            :disabled="isDisabled"
                                        >
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-300" id="file_input_help">Format accepté : ZIP</p>
                                        <InputError class="mt-2" :message="form.errors.file_cover" />
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-end pt-6">
                                <PrimaryButton
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing || isDisabled"
                                >
                                    Transférer
                                </PrimaryButton>
                            </div>
                        </div>
                    </div> -->
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>