let mix = require('laravel-mix');

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
.scripts([
   'resources/assets/js/jquery.min.js',
   'resources/assets/js/bootstrap.min.js',
   'resources/assets/js/vue.min.js',
   'resources/assets/js/axios.min.js',
   'resources/assets/js/toastr.min.js',
   'resources/assets/js/moment.min.js',
   'resources/assets/js/sb-admin-2.js',
   'resources/assets/js/metisMenu.min.js',
   'resources/assets/js/app.js'
], 'public/js/app.js')
.styles([
   'resources/assets/css/app.css',
   'resources/assets/css/toastr.min.css',
   'resources/assets/css/metisMenu.min.css',
   'resources/assets/css/sb-admin-2.css',
   'resources/assets/css/open-iconic-bootstrap.css'
], 'public/css/app.css');

