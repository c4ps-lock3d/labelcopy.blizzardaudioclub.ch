<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Success from '@/Components/Success.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';
defineProps({
    message: {
        type: String,
        default: null
    },
    customClass: {
        type: String,
        default: ''
    }
});

const form = useForm({
    catalog: '',
    email: '',
    firstname: '',
    lastname: '',
    is_reference: true,
    isActive: true,
});

const emailExists = ref(false);
const successMessage = ref('');

const checkEmail = async () => {
    if (form.email) {
        const response = await axios.post(route('check-email'), { email: form.email });
        emailExists.value = response.data.exists;

        if (emailExists.value) {
            form.firstname = response.data.firstname || '';
            form.lastname = response.data.lastname || '';
            successMessage.value = `Un compte existe déjà pour ${form.firstname} ${form.lastname}. Les champs ont été pré-remplis.`;
        } else {
            form.firstname = '';
            form.lastname = '';
            successMessage.value = '';
        }
    }
};

const submit = () => {
    form.post(route('store-release'));
};
</script>

<template>
    <Head title="Ajouter une sortie" />

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
            > Ajouter une sortie
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium flex items-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 mr-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                            </svg>
                            Informations préliminaires
                        </h3>
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 mb-6">
                                <div>
                                    <InputLabel for="catalog" value="N° de catalogue" class="required" />
                                    <TextInput
                                        id="catalog"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.catalog"
                                        required
                                        autofocus
                                        autocomplete="catalog"
                                    />
                                    <InputError class="mt-2" :message="form.errors.catalog" />
                                </div>
                                <div class="">
                                    <InputLabel for="email" value="E-mail de l'artiste de référence" class="required"/>
                                    <TextInput
                                        id="email"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.email"
                                        @input="checkEmail"
                                        required
                                        autocomplete="email"
                                    />
                                    <InputError class="mt-2" :message="form.errors.email" />
                                </div>
                            </div>
                            <Success v-if="emailExists" :class="['mb-4', customClass]" :message="successMessage" />
                            <div v-if="form.catalog && form.email" class="grid grid-cols-1 gap-6 md:grid-cols-2 w-full">
                                <div>
                                    <InputLabel for="firstname" value="Prénom de l'artiste de référence" class="required"/>
                                    <TextInput
                                        id="firstname"
                                        type="text"
                                        :class="{
                                                    '!bg-gray-700/10': emailExists,
                                                    'w-full mt-1 block transition duration-150 ease-in-out': true
                                                }"
                                        v-model="form.firstname"
                                        autocomplete="firstname"
                                        required
                                        :disabled="emailExists"
                                    />
                                    <InputError class="mt-2" :message="form.errors.firstname" />
                                </div>
                                <div>
                                    <InputLabel for="lastname" value="Nom de l'artiste de référence" class="required"/>
                                    <TextInput
                                        id="lastname"
                                        type="text"
                                        :class="{
                                                    '!bg-gray-700/10': emailExists,
                                                    'w-full mt-1 block transition duration-150 ease-in-out': true
                                                }"
                                        v-model="form.lastname"
                                        autocomplete="lastname"
                                        required
                                        :disabled="emailExists"
                                    />
                                    <InputError class="mt-2" :message="form.errors.lastname" />
                                </div>
                            </div>
                            <div v-if="emailExists" class="mt-8 flex items-center justify-end">
                                <PrimaryButton
                                    class="ms-4"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Créer une nouvelle sortie
                                </PrimaryButton>
                            </div>
                            <div v-else class="mt-8 flex items-center justify-end">
                                <PrimaryButton
                                    class="ms-4"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Créer une nouvelle sortie et envoyer l'invitation
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
 