const mix = require('laravel-mix');

mix.setPublicPath('public');
mix.setResourceRoot('../');


mix
    .js('src/assets/js/ff-booking-public-view.js', 'public/js/ff-booking-public-view.js')
    .js('src/assets/js/ff-booking-date-time.js', 'public/js/ff-booking-date-time.js')
    .js('src/assets/BookingSettings/booking-settings.js', 'public/js/booking-settings.js')
    .sass('src/assets/BookingSettings/booking-settings.scss', 'public/js/booking-settings.css')
    .sass('src/assets/css/form_booking.scss', 'public/css/form_booking.css')

