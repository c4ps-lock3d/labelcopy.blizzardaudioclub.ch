import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import daisyui from 'daisyui';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [
        forms,
        daisyui
    ],

    // Configuration DaisyUI
    daisyui: {
        themes: ["light", "dark"], // Seulement les thèmes light et dark
        darkTheme: "dark", // Le thème à utiliser quand dark mode est activé
        base: true, // Appliquer les styles de base
        styled: true, // Inclure les styles par défaut
        utils: true, // Inclure les classes utilitaires
        prefix: "", // Préfixe pour les classes (ex: "daisy-btn" au lieu de "btn")
        logs: false, // Désactiver les logs dans la console
    },
};
