const colors = require('tailwindcss/colors')

module.exports = {
  purge: [
    './**/*.vue',
  ],
  // important: true,
  corePlugins: {
    preflight: false,
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
    require('@tailwindcss/aspect-ratio'),
  ],
  theme: {
    extend: {
      colors: {
        'light-blue': colors.lightBlue,
        teal: colors.teal,
        rose: colors.rose,
      }
    }
  }
}