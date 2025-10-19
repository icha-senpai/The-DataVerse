<?php
// Debug helper: bootstrap the application and attempt to flush cache with full exception output.
// Run: php debug_clear_cache.php

error_reporting(E_ALL);
ini_set('display_errors', '1');

$base = __DIR__;
require $base . '/bootstrap/autoload.php';
$app = require_once $base . '/bootstrap/app.php';

try {
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    echo "Application bootstrapped.\n";

    $cache = $app['cache'];
    $store = $cache->getStore();
    echo "Cache store class: " . get_class($store) . "\n";

    $result = $cache->flush();
    echo "cache->flush() returned: " . ($result ? 'true' : 'false') . "\n";

    // Also try lower-level file store clear if file store
    if (get_class($store) === 'Illuminate\Cache\FileStore' || is_subclass_of($store, 'Illuminate\Cache\FileStore')) {
        $path = $store->getDirectory();
        echo "File store directory: $path\n";
        // list directory
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS));
        foreach ($files as $file) {
            echo "F: " . $file->getPathname() . " (perm: " . sprintf('%o', $file->getPerms() & 0777) .")\n";
        }
    }

} catch (Throwable $e) {
    echo "Caught throwable:\n" . $e->__toString() . "\n";
}

?>