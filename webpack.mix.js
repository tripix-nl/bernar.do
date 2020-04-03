
const mix = require('laravel-mix'),
    tailwindcss = require('tailwindcss'),
    purgeCss = require('laravel-mix-purgecss');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
    .less('resources/less/app.less', 'public/css', {}, [tailwindcss('./tailwind.config.js')])
    .purgeCss({
        whitelistPatterns: [/^hljs/],
    })
    .version();
