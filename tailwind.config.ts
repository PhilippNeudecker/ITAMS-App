/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.ts',
    './resources/**/*.vue',
    "./node_modules/@shadcn/ui/dist/**/*.{js,ts,jsx,tsx}",],
  theme: {
    extend: {},
  },
  plugins: [require("tailwindcss-animate")],
}

