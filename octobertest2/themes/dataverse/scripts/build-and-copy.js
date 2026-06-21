const { spawnSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const themeRoot = path.resolve(__dirname, '..');
const cssDir = path.join(themeRoot, 'assets', 'css');
const inputCss = path.join(cssDir, 'input.css');
const outputCss = path.join(cssDir, 'output.css');
const vendorDir = path.join(themeRoot, 'assets', 'vendor', 'tailwind');
const vendorCss = path.join(vendorDir, 'tailwind.css');
const tailwindCli = path.join(themeRoot, 'node_modules', '@tailwindcss', 'cli', 'dist', 'index.mjs');

const usePostCSS = process.argv.includes('--postcss');

function runTailwindBuild() {
  const args = [tailwindCli, '-i', inputCss, '-o', outputCss, '--minify'];

  console.log('[build-and-copy] running:', process.execPath, args.join(' '));
  const proc = spawnSync(process.execPath, args, { cwd: themeRoot, stdio: 'inherit' });
  return proc.status || (proc.error ? 1 : 0);
}

function loadPostcssPlugins() {
  const postcssConfigPath = path.join(themeRoot, 'postcss.config.js');
  const postcssConfig = require(postcssConfigPath);
  const pluginEntries = Object.entries(postcssConfig.plugins || {});

  return pluginEntries
    .filter(([, options]) => options !== false)
    .map(([pluginName, options]) => {
      const pluginFactory = require(pluginName);

      if (typeof pluginFactory !== 'function') {
        return pluginFactory;
      }

      if (options === true || options == null) {
        return pluginFactory();
      }

      return pluginFactory(options);
    });
}

async function runPostcssBuild() {
  const previousIgnoreOldData = process.env.BROWSERSLIST_IGNORE_OLD_DATA;
  process.env.BROWSERSLIST_IGNORE_OLD_DATA = '1';

  const originalWarn = console.warn;
  console.warn = (...args) => {
    const message = args.map((arg) => String(arg)).join(' ');

    if (message.includes('[baseline-browser-mapping] The data in this module is over two months old.')) {
      return;
    }

    originalWarn(...args);
  };

  const postcss = require('postcss');
  try {
    const sourceCss = fs.readFileSync(inputCss, 'utf8');
    const processor = postcss(loadPostcssPlugins());
    const result = await processor.process(sourceCss, {
      from: inputCss,
      to: outputCss,
    });

    for (const warning of result.warnings()) {
      console.warn('[build-and-copy] postcss warning:', warning.toString());
    }

    fs.writeFileSync(outputCss, result.css, 'utf8');
    return 0;
  }
  finally {
    console.warn = originalWarn;

    if (previousIgnoreOldData == null) {
      delete process.env.BROWSERSLIST_IGNORE_OLD_DATA;
    } else {
      process.env.BROWSERSLIST_IGNORE_OLD_DATA = previousIgnoreOldData;
    }
  }
}

function copyToVendor() {
  try {
    fs.mkdirSync(vendorDir, { recursive: true });
    if (!fs.existsSync(outputCss)) {
      console.error('[build-and-copy] expected output file not found:', outputCss);
      return 2;
    }
    fs.copyFileSync(outputCss, vendorCss);
    console.log('[build-and-copy] copied', outputCss, '->', vendorCss);
    return 0;
  } catch (err) {
    console.error('[build-and-copy] copy to vendor failed:', err && err.message ? err.message : err);
    return 3;
  }
}

async function main() {
  const code = usePostCSS
    ? await runPostcssBuild()
    : runTailwindBuild();

  if (code !== 0) {
    console.error('[build-and-copy] build failed with code', code);
    process.exit(code);
  }

  const c = copyToVendor();
  if (c !== 0) process.exit(c);

  console.log('[build-and-copy] SUCCESS');
}

main().catch((err) => {
  console.error('[build-and-copy] build failed:', err && err.message ? err.message : err);
  process.exit(1);
});
