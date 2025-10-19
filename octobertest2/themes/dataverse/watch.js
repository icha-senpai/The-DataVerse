#!/usr/bin/env node
const { spawn } = require('child_process');
const path = require('path');

// Thin wrapper: delegate to the unified watcher so watch mode uses the single-run flow.
const themeRoot = path.resolve(__dirname);
const watcher = path.join(themeRoot, 'scripts', 'watch-and-copy-clean.js');
if (!require('fs').existsSync(watcher)) {
  console.error('Unified watcher not found at', watcher);
  process.exit(1);
}

const usePostcss = process.argv.includes('--postcss');
const args = [watcher].concat(usePostcss ? ['--postcss'] : []);
console.log('Starting unified watcher:', process.execPath, args.join(' '));

const proc = spawn(process.execPath, args, { cwd: themeRoot, stdio: 'inherit' });
proc.on('exit', (code) => process.exit(code));