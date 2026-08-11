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
                // SPEC.md §3.4: the Thaana face must NEVER be the global sans
                // family — English renders in the Latin stack. The Thaana face
                // is opt-in through the `font-thaana` utility below, which the
                // layouts apply to <body> only when the locale is `dv`.
                sans: ['system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
                thaana: ['var(--font-thaana)'],
            },
            colors: {
                navy: {
                    DEFAULT: '#0B1F3A',
                    50: '#EBEEF3',
                    100: '#D1D9E4',
                    200: '#A3B3C9',
                    300: '#758DAE',
                    400: '#4A6693',
                    500: '#0B1F3A',
                    600: '#091A31',
                    700: '#071526',
                    800: '#05101C',
                    900: '#030A12',
                },
                gold: {
                    DEFAULT: '#C9A227',
                    50: '#FBF5E3',
                    100: '#F5E7BE',
                    200: '#ECD48A',
                    300: '#E2C056',
                    400: '#D6AF3F',
                    500: '#C9A227',
                    600: '#A6851F',
                    700: '#7D6417',
                    800: '#54430F',
                    900: '#2B2208',
                },
            },
        },
    },

    plugins: [forms],
};
