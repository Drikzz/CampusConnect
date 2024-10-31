import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                'Header': ['FontSpring-hvy', 'sans-serif'],
                'Satoshi': ['Satoshi', 'sans-serif'],
                'Footer': ['FontSpring-demi', 'sans-serif']
            },

            colors: {
                'primary-color': '#8D0A0A',
            }
        },
    },
    plugins: [],
};
