module.exports = {
  plugins: {
    'postcss-import': {},
    'postcss-nesting': {},
    'postcss-custom-media': {},
    'postcss-flexbugs-fixes': {},
    '@tailwindcss/postcss': {},   // 👈 NEW: proper adapter for Tailwind 4
    autoprefixer: {},
    cssnano: {
      preset: ['default', {
        // DaisyUI emits calc(infinity * 1px) in a few component rules.
        // Disabling calc folding avoids noisy warnings without weakening the rest of cssnano.
        calc: false,
      }],
    },
  },
};
