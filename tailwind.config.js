import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        "./node_modules/flowbite/**/*.js",
        "vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php"
    ],
    safelist: [
        'bg-primary-color',
        'text-primary-color',
        'border-primary-color',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                'Header': ['FontSpring-hvy', 'sans-serif'],
                'Footer': ['FontSpring-demi', 'sans-serif'],
                'FontSpring-bold' : ['FontSpring-bold', 'sans-serif'],
                'FontSpring-extra-bold' : ['FontSpring-extra-bold', 'sans-serif'],
                'Satoshi': ['Satoshi', 'sans-serif'],
                'Satoshi-bold': ['Satoshi-bold', 'sans-serif'],
                'FontSpring-bold': ['FontSpring-bold', 'sans-serif'],
                'FontSpring-bold-oblique': ['FontSpring-bold-oblique', 'sans-serif'],
            },

            colors: {
                'primary-color': '#8D0A0A',
                'footer': '#F0F0F0',
                'red': '#FF0000',
            }
        },
    },
    plugins: [
        require('flowbite/plugin')({
            charts: true,
        }),
    ],
};