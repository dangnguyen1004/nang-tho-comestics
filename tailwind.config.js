/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',
  content: [
    './wp-content/themes/nang-tho-cosmetics/**/*.php',
    './wp-content/themes/nang-tho-cosmetics/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        "primary": "#ee2b8c",
        "background-light": "#f8f6f7",
        "background-dark": "#221019",
        "text-dark": "#1b0d14",
      },
      fontFamily: {
        "sans": ["Manrope", "sans-serif"],
      },
    },
  },
  plugins: [],
}
