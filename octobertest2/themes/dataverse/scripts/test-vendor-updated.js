const { spawnSync } = require('child_process');
const fs = require('fs');
const path = require('path');

const themeRoot = path.resolve(__dirname, '..');
const outputCss = path.join(themeRoot, 'assets', 'css', 'output.css');
const vendorCss = path.join(themeRoot, 'assets', 'vendor', 'tailwind', 'tailwind.css');

function fileExists(p) {
  try { return fs.existsSync(p); } catch (_) { return false; }
}

console.log('Running theme build to produce', outputCss);
// Use platform-aware npm command
const npmCmd = process.platform === 'win32' ? 'npm.cmd' : 'npm';
const build = spawnSync(npmCmd, ['run', 'build'], { cwd: themeRoot, stdio: 'inherit', shell: true });
if (build.error) {
  console.error('Build command failed:', build.error);
  process.exit(2);
}
if (build.status !== 0) {
  console.error('Build exited with code', build.status);
  process.exit(build.status || 1);
}

if (!fileExists(outputCss)) {
  console.error('Expected output file not found:', outputCss);
  process.exit(3);
}
if (!fileExists(vendorCss)) {
  console.error('Expected vendor file not found after build:', vendorCss);
  process.exit(4);
}

const outBuf = fs.readFileSync(outputCss);
const vendBuf = fs.readFileSync(vendorCss);

if (outBuf.equals(vendBuf)) {
  console.log('SUCCESS: vendor file matches output.css');
  process.exit(0);
} else {
  console.error('FAIL: vendor file does not match output.css');
  // Optionally write a diff summary
  console.error('Sizes -> output:', outBuf.length, 'vendor:', vendBuf.length);
  process.exit(5);
}
