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
                sans: ['Sora', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: 'rgb(var(--primary) / <alpha-value>)',
                'primary-content': 'rgb(var(--primary-content) / <alpha-value>)',
                secondary: 'rgb(var(--secondary) / <alpha-value>)',
                'secondary-content': 'rgb(var(--secondary-content) / <alpha-value>)',
                accent: 'rgb(var(--accent) / <alpha-value>)',
                'accent-content': 'rgb(var(--accent-content) / <alpha-value>)',
                neutral: 'rgb(var(--neutral) / <alpha-value>)',
                'neutral-content': 'rgb(var(--neutral-content) / <alpha-value>)',
                'base-100': 'rgb(var(--base-100) / <alpha-value>)',
                'base-200': 'rgb(var(--base-200) / <alpha-value>)',
                'base-300': 'rgb(var(--base-300) / <alpha-value>)',
                'base-content': 'rgb(var(--base-content) / <alpha-value>)',
                info: 'rgb(var(--info) / <alpha-value>)',
                success: 'rgb(var(--success) / <alpha-value>)',
                warning: 'rgb(var(--warning) / <alpha-value>)',
                error: 'rgb(var(--error) / <alpha-value>)',
            },
        },
    },

    plugins: [forms],
};
