const fs = require('fs');
const path = require('path');
const { spawn } = require('child_process');

// watch-and-copy-clean.js
// Clean watcher: runs tailwind/postcss in watch mode and copies output.css to vendor when updated.
const themeRoot = path.resolve(__dirname, '..');
const cssDir = path.join(themeRoot, 'assets', 'css');
const outputCss = path.join(cssDir, 'output.css');

const usePostCSS = process.argv.includes('--postcss');

// Watcher that invokes the single-run build script on changes.
// This keeps watch mode consistent with the single-run flow (it runs build-and-copy.js).
function startWatcher() {
  const buildScript = path.join(themeRoot, 'scripts', 'build-and-copy.js');
  if (!fs.existsSync(buildScript)) {
    console.error('[watch-and-copy-clean] build script not found at', buildScript);
    process.exit(1);
  }

  let building = false;
  let queued = false;
  let debounceTimer = null;

  function runBuild() {
    if (building) {
      queued = true;
      return;
    }
    building = true;
    queued = false;

    const args = [buildScript].concat(usePostCSS ? ['--postcss'] : []);
    console.log('[watch-and-copy-clean] running build:', process.execPath, args.join(' '));
    const p = spawn(process.execPath, args, { cwd: themeRoot, stdio: 'inherit' });

    p.on('exit', (code) => {
      building = false;
      if (code !== 0) console.error('[watch-and-copy-clean] build exited with code', code);
      if (queued) {
        // schedule the queued build shortly after current finishes
        setTimeout(runBuild, 100);
      }
    });
  }

  function scheduleBuild() {
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      debounceTimer = null;
      runBuild();
    }, 200);
  }

  // Watch the CSS directory and tailwind config for changes that should trigger a rebuild.
  try {
    fs.watch(path.join(themeRoot, 'assets', 'css'), { persistent: true }, (ev, fn) => {
      if (!fn) return;
      const name = String(fn || '');
      // watch for changes to input.css or any CSS in the folder
      if (name.endsWith('.css')) scheduleBuild();
    });

    const tailwindConfig = path.join(themeRoot, 'tailwind.config.js');
    if (fs.existsSync(tailwindConfig)) {
      fs.watchFile(tailwindConfig, { interval: 500 }, (curr, prev) => {
        if (curr.mtimeMs !== prev.mtimeMs) scheduleBuild();
      });
    }
  } catch (err) {
    console.warn('[watch-and-copy-clean] failed to attach fs.watch; falling back to polling input.css');
    const inputCss = path.join(themeRoot, 'assets', 'css', 'input.css');
    let lastM = 0;
    setInterval(() => {
      try {
        const st = fs.statSync(inputCss);
        if (st.mtimeMs > lastM) {
          lastM = st.mtimeMs;
          scheduleBuild();
        }
      } catch (e) {
        // ignore until file exists
      }
    }, 1000);
  }

  // initial build to ensure vendor is up to date
  runBuild();
}

if (!fs.existsSync(cssDir)) {
  console.error('[watch-and-copy-clean] CSS directory not found at', cssDir);
  process.exit(1);
}

startWatcher();
