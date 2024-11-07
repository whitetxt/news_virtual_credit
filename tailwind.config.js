/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.php",
    "./script/*.js",
    "./**/*.php",
    "./**/*.html",
    "./**/**/*.php",
    "./**/**/*.html",
  ],
  theme: {
    extend: {},
  },
  plugins: [require("daisyui")],
  daisyui: {
    themes: ["dark"],
  },
};
