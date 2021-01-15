const mix = require('laravel-mix');
const tailwindcss = require('tailwindcss');

mix
  .js('resources/js/admin/main.js', 'public/js/admin.js')
  .vue()
  .extract()
  .sass('resources/sass/app.scss', 'public/css')
  .options({
    processCssUrls: false,
    postCss: [ tailwindcss('./tailwind.config.js') ],
  })
  .copy('resources/img', 'public')

  .disableNotifications();
