<?php

namespace XF;

use XF\Util\File;

class ComposerAutoload
{
    /**
     * @var App
     */
    protected $app;

    protected $pathPrefix;

    protected $checkPaths = true;

    public function __construct(App $app, $pathPrefix)
    {
        $this->app = $app;
        $this->pathPrefix = rtrim($pathPrefix, '\/') . \XF::$DS;
    }

    public function checkPaths(bool $check)
    {
        $this->checkPaths = $check;
    }

    public function autoloadAll($prepend = false)
    {
        $this->autoloadNamespaces($prepend);
        $this->autoloadPsr4($prepend);
        $this->autoloadClassmap();
        $this->autoloadFiles();
    }

    public function autoloadNamespaces($prepend = false)
    {
        $namespaces = $this->pathPrefix . 'autoload_namespaces.php';

        if (!file_exists($namespaces)) {
            \XF::logError("[ComposerAutoload] Skipped missing namespaces file: {$namespaces}");
            return;
        }

        $map = require $namespaces;
        if (is_array($map)) {
            foreach ($map as $namespace => $path) {
                \XF::$autoLoader->add($namespace, $path, $prepend);
            }
        }
    }

    public function autoloadPsr4($prepend = false)
    {
        $psr4 = $this->pathPrefix . 'autoload_psr4.php';

        if (!file_exists($psr4)) {
            \XF::logError("[ComposerAutoload] Skipped missing PSR4 file: {$psr4}");
            return;
        }

        $map = require $psr4;
        if (is_array($map)) {
            foreach ($map as $namespace => $path) {
                \XF::$autoLoader->addPsr4($namespace, $path, $prepend);
            }
        }
    }

    public function autoloadClassmap()
    {
        $classmap = $this->pathPrefix . 'autoload_classmap.php';

        if (!file_exists($classmap)) {
            \XF::logError("[ComposerAutoload] Skipped missing classmap file: {$classmap}");
            return;
        }

        $map = require $classmap;
        if (is_array($map) && $map) {
            \XF::$autoLoader->addClassMap($map);
        }
    }

    public function autoloadFiles()
    {
        $files = $this->pathPrefix . 'autoload_files.php';

        if (!file_exists($files)) {
            \XF::logError("[ComposerAutoload] Skipped missing files autoloader: {$files}");
            return;
        }

        $includeFiles = require $files;

        if (is_array($includeFiles)) {
            foreach ($includeFiles as $fileIdentifier => $file) {
                if (empty($GLOBALS['__composer_autoload_files'][$fileIdentifier])) {
                    if (file_exists($file)) {
                        require $file;
                        $GLOBALS['__composer_autoload_files'][$fileIdentifier] = true;
                    } else {
                        \XF::logError("[ComposerAutoload] Missing autoload include file: {$file}");
                    }
                }
            }
        }
    }

    protected function getPathForError($path)
    {
        return File::stripRootPathPrefix($path);
    }
}
