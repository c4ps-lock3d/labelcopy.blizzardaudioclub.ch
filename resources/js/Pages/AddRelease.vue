<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const form = useForm({
    catalog: '',
    email: '',
    firstname: '',
    lastname: '',
    is_reference: true,
    isActive: true,
});

const emailExists = ref(false);

const checkEmail = async () => {
    if (form.email) {
        const response = await axios.post(route('check-email'), { email: form.email });
        emailExists.value = response.data.exists;

        if (emailExists.value) {
            form.firstname = response.data.firstname || '';
            form.lastname = response.data.lastname || '';
        } else {
            form.firstname = '';
            form.lastname = '';
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
                Dashboard > Ajouter une sortie
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                >
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit">
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
                            <div class="mt-4">
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
                            <div v-if="emailExists" class="mt-4 text-sm text-red-500">E-mail trouvé ! Artiste déjà existant :</div>
                            <div class="mt-4">
                                <InputLabel for="firstname" value="Prénom de l'artiste de référence" class="required"/>
                                <TextInput
                                    id="firstname"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.firstname"
                                    autocomplete="firstname"
                                    :disabled="emailExists"
                                />
                                <InputError class="mt-2" :message="form.errors.firstname" />
                            </div>
                            <div class="mt-4">
                                <InputLabel for="lastname" value="Nom de l'artiste de référence" class="required"/>
                                <TextInput
                                    id="lastname"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.lastname"
                                    autocomplete="lastname"
                                    :disabled="emailExists"
                                />
                                <InputError class="mt-2" :message="form.errors.lastname" />
                            </div>
                            <div v-if="emailExists" class="mt-4 flex items-center justify-end">
                                <PrimaryButton
                                    class="ms-4"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Créer une nouvelle sortie
                                </PrimaryButton>
                            </div>
                            <div v-else class="mt-4 flex items-center justify-end">
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
 