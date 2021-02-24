const mix = require('laravel-mix');

mix.setPublicPath('public');
mix.setResourceRoot('../');


mix
    .js('src/assets/js/tinyMceInit.js', 'public/js/tinyMceInit.js')
    .js('src/assets/BookingSettings/booking-settings.js', 'public/js/booking-settings.js')

