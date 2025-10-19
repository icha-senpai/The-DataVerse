module.exports = {
  plugins: {
    'postcss-import': {},
    'postcss-nesting': {},
    'postcss-custom-media': {},
    'postcss-flexbugs-fixes': {},
    '@tailwindcss/postcss': {},   // 👈 NEW: proper adapter for Tailwind 4
    autoprefixer: {},
    cssnano: { preset: 'default' },
  },
};