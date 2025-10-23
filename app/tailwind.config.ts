/**
 * Tailwind configuration for the `app` workspace.
 * The installed Tailwind CLI in this repo does not provide `tailwindcss init`.
 * Creating this file manually ensures the CLI has the content paths and basic settings.
 */
/* eslint-disable import/order */
import type { Config } from 'tailwindcss'
// @ts-expect-error daisyUI has no types
import daisyui from 'daisyui'
import flowbite from 'flowbite/plugin'
 

export default {
  content: [
    './src/**/*.{html,js,ts,jsx,tsx}',
    '../**/*.{htm,html,php,twig,js,jsx,ts,tsx}',
    '../../octobertest2/themes/**/assets/**/*.{htm,html,php,twig,js}',
  ],
  theme: {
    extend: {
      colors: {
        dv: { surface: '#0a0a0f', accent: '#00eaff', accent2: '#ff00aa' },
      },
      fontFamily: {
        tilt: ['Tilt Neon', 'sans-serif'],
        orbitron: ['Orbitron', 'sans-serif'],
        inter: ['Inter', 'sans-serif'],
      },
    },
  },
  plugins: [daisyui, flowbite],
} satisfies Config