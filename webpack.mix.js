const mix = require('laravel-mix');
const tailwindcss = require('tailwindcss');

mix
  .js('resources/js/admin/main.js', 'public/js/admin.js')
  .js('resources/js/frontend/main.js', 'public/js/frontend.js')
  .vue()
  .extract()
  .sass('resources/sass/admin/app.scss', 'public/css/admin.css', {}, [
    tailwindcss('resources/js/admin/tailwind.config.js')
  ])
  .sass('resources/sass/frontend/app.scss', 'public/css/frontend.css', {}, [
    tailwindcss('resources/js/frontend/tailwind.config.js')
  ])
  .options({
    processCssUrls: false,
  })
  .copy('resources/img', 'public/img')
  .disableNotifications();
