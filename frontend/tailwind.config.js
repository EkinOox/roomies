/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './index.html',
    './src/**/*.{vue,js,ts,jsx,tsx}',
  ],
  // darkMode: 'class', // Active le dark mode basé sur une classe <html class="dark">
  theme: {
    extend: {
      colors: {
        teal: '#3EC6B5',
        coral: '#FF6B6B',
        violet: {
          soft: '#A98FE3',
        },
        background: {
          light: '#F9F9F9',
          dark: '#181818',
        },
        gray: {
          light: '#f2f2f2',
          soft: '#e0e0e0',
          dark: '#2a2a2a',
        },
        text: {
          light: '#2c3e50',
          dark: '#f2f2f2',
        },
      },
      shadow: {
        neumorph: '8px 8px 15px #a3b1c6, -8px -8px 15px #ffffff',
        'neumorph-inner': 'inset 8px 8px 15px #a3b1c6, inset -8px -8px 15px #ffffff',
      },
      backgroundImage: {
        'gradient-teal-coral': 'linear-gradient(135deg, #3EC6B5, #FF6B6B)',
        'gradient-violet-teal': 'linear-gradient(135deg, #A98FE3, #3EC6B5)',
      },
    },
  },
  plugins: [],
}
