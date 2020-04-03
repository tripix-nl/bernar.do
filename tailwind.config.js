
const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    theme: {
        screens: {
            sm: '640px',
            md: '768px',
            lg: '1024px',
        },
        extend: {
            fontFamily: {
                serif: ["IBM Plex Serif", ...defaultTheme.fontFamily.serif],
                mono: ["IBM Plex Mono", ...defaultTheme.fontFamily.mono],
            },
        },
    },
    variants: {},
    plugins: [],
};
