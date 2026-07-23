/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    blue: {
                        50: '#EEF5FF',
                        100: '#D9E9FF',
                        600: '#155EEF',
                        700: '#124E8C',
                        900: '#0B3B75',
                    },
                    red: {
                        50: '#FEF3F2',
                        700: '#B42318',
                    },
                },
                ink: {
                    900: '#172033',
                },
                cloud: {
                    50: '#F4F7FB',
                },
                service: {
                    mobility: '#2563EB',
                    immigration: '#7C3AED',
                    nationality: '#A15C00',
                    education: '#047857',
                    civil: '#BE185D',
                    business: '#0F766E',
                },
            },
            fontFamily: {
                display: ['Archivo', 'Arial', 'sans-serif'],
                sans: ['"Source Sans 3"', 'Arial', 'sans-serif'],
                mono: ['"IBM Plex Mono"', 'Consolas', 'monospace'],
            },
            boxShadow: {
                focus: '0 0 0 3px rgba(21, 94, 239, 0.35)',
            },
            ringWidth: {
                3: '3px',
            },
        },
    },
    plugins: [],
};
