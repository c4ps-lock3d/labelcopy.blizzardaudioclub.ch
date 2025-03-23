<script setup>
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { defineProps } from 'vue';

const props = defineProps({
    user: {
        type: Object,
        required: true
    }
});

const form = useForm({
    email: props.user.email,
    password: '',
    password_confirmation: ''
});

const submit = () => {
    form.post(route('welcomesave', props.user.id)); // Inclure l'ID de l'utilisateur dans la route
};
</script>

<template>
    <GuestLayout>
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-gray-100">
                Définir un mot de passe
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                Veuillez définir un mot de passe pour activer votre compte.
            </p>
        </div>

        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="email" value="Adresse e-mail" />
                        <TextInput
                            id="email"
                            type="text"
                            class="!bg-gray-800 mt-1 block w-full transition duration-150 ease-in-out"
                            v-model="form.email"
                            required
                            disabled
                            autocomplete="new-password"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>
                    
                    <div>
                        <InputLabel for="password" value="Mot de passe" />
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                        />
                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <div>
                        <InputLabel for="password_confirmation" value="Confirmer le mot de passe" />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                        />
                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>

                    <div>
                        <PrimaryButton
                            class="w-full justify-center"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Enregistrer et se connecter
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>