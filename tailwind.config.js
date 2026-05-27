import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            // AQUÍ AGREGAMOS TUS COLORES PERSONALIZADOS
            colors: {
                brand: {
                    DEFAULT: '#7e22ce', // Morado principal
                    light: '#e9d5ff',   // Morado muy claro
                    dark: '#6b21a8',    // Morado oscuro
                },
            },
        },
    },

    plugins: [forms],
};