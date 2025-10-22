module.exports = {
  content: [
    './themes/**/*.htm',
    './themes/**/*.html',
    './themes/**/*.twig',
    './themes/**/*.js',
    './layouts/**/*.htm',
    './pages/**/*.htm',
    './partials/**/*.htm',
    './plugins/**/*.php',
    './modules/**/*.{php,js,vue,ts}',
  ],
  important: '.tv',
  darkMode: 'class', // or 'media'
  theme: {
    extend: {
      fontFamily: {
        tilt: ['"Tilt Neon"', 'Inter', 'ui-sans-serif', 'system-ui'],
      },
      boxShadow: {
        neon: '0 0 15px rgba(0, 255, 255, 0.3)',
      },
      colors: {
        'dv-bg': '#000000',     // pure black
        'dv-text': '#ffffff',   // pure white
        'dv-accent': '#00ffff', // cyan glow
        'dv-accent2': '#ff00ff' // magenta glow
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
    require('@tailwindcss/aspect-ratio'),
    require('@tailwindcss/line-clamp'),
    require('@tailwindcss/container-queries'),
    require('tailwind-scrollbar'),
    require('tailwindcss-textshadow'),
    require('tailwindcss-filters'),
    require('tailwindcss-gradients'),
    require('daisyui'),
    require('flowbite/plugin'),
  ],
  safelist: [
    
  ],
}