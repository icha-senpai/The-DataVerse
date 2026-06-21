const fs = require('fs');
const path = require('path');
const { spawn } = require('child_process');

// watch-and-copy-clean.js
// Clean watcher: runs tailwind/postcss in watch mode and copies output.css to vendor when updated.
const themeRoot = path.resolve(__dirname, '..');
const cssDir = path.join(themeRoot, 'assets', 'css');
const usePostCSS = process.argv.includes('--postcss');
const watchedDirectories = [
  path.join(themeRoot, 'assets', 'css'),
  path.join(themeRoot, 'assets', 'js'),
  path.join(themeRoot, 'layouts'),
  path.join(themeRoot, 'pages'),
  path.join(themeRoot, 'partials'),
  path.join(themeRoot, 'content'),
];
const watchedFiles = [
  path.join(themeRoot, 'tailwind.config.js'),
  path.join(themeRoot, 'postcss.config.js'),
  path.join(themeRoot, 'package.json'),
];

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

  // Watch the theme files Tailwind scans so class changes rebuild immediately.
  try {
    for (const dir of watchedDirectories) {
      if (!fs.existsSync(dir)) continue;

      fs.watch(dir, { persistent: true, recursive: true }, (ev, fn) => {
        if (!fn) return;

        const name = String(fn || '').toLowerCase();
        if (/\.(css|js|htm|html|twig|php|vue|ts)$/.test(name)) {
          scheduleBuild();
        }
      });
    }

    for (const filePath of watchedFiles) {
      if (!fs.existsSync(filePath)) continue;

      fs.watchFile(filePath, { interval: 500 }, (curr, prev) => {
        if (curr.mtimeMs !== prev.mtimeMs) scheduleBuild();
      });
    }
  } catch (err) {
    console.warn('[watch-and-copy-clean] failed to attach fs.watch; falling back to polling theme sources');
    const polledPaths = watchedDirectories.concat(watchedFiles).filter((filePath) => fs.existsSync(filePath));
    const lastSeen = new Map();

    setInterval(() => {
      try {
        for (const filePath of polledPaths) {
          const st = fs.statSync(filePath);
          const previous = lastSeen.get(filePath) || 0;

          if (st.mtimeMs > previous) {
            lastSeen.set(filePath, st.mtimeMs);
            scheduleBuild();
          }
        }
      } catch (e) {
        // ignore until paths exist
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
