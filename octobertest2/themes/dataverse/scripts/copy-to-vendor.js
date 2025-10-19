const fs = require('fs');
const path = require('path');

const themeRoot = path.resolve(__dirname, '..');
const src = path.join(themeRoot, 'assets', 'css', 'output.css');
const destDir = path.join(themeRoot, 'assets', 'vendor', 'tailwind');
const dest = path.join(destDir, 'tailwind.css');

try {
  if (!fs.existsSync(src)) {
    console.error('Source file does not exist:', src);
    process.exit(1);
  }

  fs.mkdirSync(destDir, { recursive: true });
  fs.copyFileSync(src, dest);
  console.log('copied', src, '->', dest);
  process.exit(0);
} catch (err) {
  console.error('Failed to copy vendor file:', err && err.message ? err.message : err);
  process.exit(2);
}
