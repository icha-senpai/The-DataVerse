const { spawnSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const themeRoot = path.resolve(__dirname, '..');
const cssDir = path.join(themeRoot, 'assets', 'css');
const inputCss = path.join(cssDir, 'input.css');
const outputCss = path.join(cssDir, 'output.css');
const vendorDir = path.join(themeRoot, 'assets', 'vendor', 'tailwind');
const vendorCss = path.join(vendorDir, 'tailwind.css');

const usePostCSS = process.argv.includes('--postcss');
const cmd = process.platform === 'win32' ? 'npx.cmd' : 'npx';

function runBuild() {
  const args = usePostCSS
    ? ['postcss', inputCss, '-o', outputCss, '--env', 'production']
    : ['tailwindcss', '-i', inputCss, '-o', outputCss, '--minify'];

  console.log('[build-and-copy] running:', cmd, args.join(' '));
  const proc = spawnSync(cmd, args, { cwd: themeRoot, stdio: 'inherit', shell: true });
  return proc.status || (proc.error ? 1 : 0);
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

function main() {
  const code = runBuild();
  if (code !== 0) {
    console.error('[build-and-copy] build failed with code', code);
    process.exit(code);
  }

  const c = copyToVendor();
  if (c !== 0) process.exit(c);

  console.log('[build-and-copy] SUCCESS');
}

main();
