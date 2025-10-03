/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/**/*.php",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#20B2AA',
        secondary: '#17a2b8',
      }
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
