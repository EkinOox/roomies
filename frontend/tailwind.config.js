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
        bleuNeon: '#00F0FF',
        roseNeon: '#FF4FCB',
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
        neonBlue: '#00ffff',
        neonPink: '#FF00CC',
        neonPurple: '#8A2BE2',
        neonGreen: '#39FF14',
        graySoft: '#B0B0B0',
        backgroundLight: '#0A0F2C',
      },
      boxShadow: {
        neumorph: '8px 8px 15px #a3b1c6, -8px -8px 15px #ffffff',
        'neumorph-inner': 'inset 8px 8px 15px #a3b1c6, inset -8px -8px 15px #ffffff',
        neon: '0 0 10px #00ffff, 0 0 20px #00ffff',
        pinkGlow: '0 0 10px #FF00CC, 0 0 20px #FF00CC',
      },
      backgroundImage: {
        'gradient-teal-coral': 'linear-gradient(135deg, #3EC6B5, #FF6B6B)',
        'gradient-violet-teal': 'linear-gradient(135deg, #A98FE3, #3EC6B5)',
      },
      animation: {
        'pulse-glow': 'pulseGlow 3s ease-in-out infinite',
        'float': 'float 6s ease-in-out infinite',
      },
      keyframes: {
        pulseGlow: {
          '0%, 100%': { boxShadow: '0 0 10px #00ffff' },
          '50%': { boxShadow: '0 0 20px #00ffff' },
        },
        float: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-10px)' },
        },
      },
    },
  },
  plugins: [],
}
