<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    catalog: '',
    email: '',
    firstname: '',
    lastname: '',
    isReference: true,
});

const submit = () => {
    form.post(route('store-release'));
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Dashboard > Ajouter une release
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
                                    required
                                    autocomplete="email"
                                />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>
                            <div class="mt-4">
                                <InputLabel for="firstname" value="Prénom de l'artiste de référence" class="required"/>
                                <TextInput
                                    id="firstname"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.firstname"
                                    required
                                    autocomplete="firstname"
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
                                    required
                                    autocomplete="lastname"
                                />
                                <InputError class="mt-2" :message="form.errors.lastname" />
                            </div>
                            <div class="mt-4 flex items-center justify-end">
                                <PrimaryButton
                                    class="ms-4"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Ajouter et envoyer l'invitation
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
 