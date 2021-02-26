const mix = require('laravel-mix');
const tailwindcss = require('tailwindcss');


mix.webpackConfig(webpack => { 
  return {
      plugins: [
          new webpack.DefinePlugin({
              // '__VUE_OPTIONS_API__': 'true',
              '__VUE_PROD_DEVTOOLS__': 'false'
          })
      ]
  }
});

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
