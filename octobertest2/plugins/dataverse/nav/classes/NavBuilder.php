<?php namespace Dataverse\Nav\Classes;

use Cms\Classes\Page;
use Symfony\Component\Yaml\Yaml;
use File;
use Illuminate\Support\Str;

class NavBuilder
{
    public static function rebuild()
    {
        $basePath = themes_path('dataverse/meta/navigation.base.yaml');
        $targetPath = themes_path('dataverse/meta/navigation.yaml');

        if (!File::exists($basePath)) {
            throw new \Exception("Base navigation file not found at {$basePath}");
        }

        // Parse the base file — this structure stays untouched
        $baseNav = Yaml::parseFile($basePath);
        $currentNav = File::exists($targetPath) ? Yaml::parseFile($targetPath) : [];
        $nav = self::mergeNav($baseNav, $currentNav);


        // Find "The Verse" node
        $verseIndex = null;
        foreach ($nav['main'] as $i => $item) {
            if (isset($item['title']) && strtolower($item['title']) === 'the verse') {
                $verseIndex = $i;
                break;
            }
        }

        if ($verseIndex === null) {
            throw new \Exception("'The Verse' section not found in base YAML");
        }

        // Gather current "The Verse" children
        $verseChildren = $nav['main'][$verseIndex]['children'] ?? [];

        // Collect existing URLs
        $existingUrls = array_unique(array_merge(
            self::collectUrls($verseChildren),
            self::collectUrls($currentNav['main'][$verseIndex]['children'] ?? [])
        ));


        // Add new CMS pages
        $pages = Page::all();
        $added = 0;

        foreach ($pages as $page) {
            if (property_exists($page, 'hidden') && $page->hidden) continue;

            $url = $page->url;
            if (!$url || $url === '/' || Str::contains($url, '404') || Str::startsWith($page->fileName, '_')) continue;
            if (in_array($url, $existingUrls)) continue;

            // Infer parent by URL prefix
            $placed = false;
            foreach ($verseChildren as &$child) {
                if (isset($child['url']) && Str::startsWith($url, $child['url'] . '/')) {
                    if (!isset($child['children'])) $child['children'] = [];
                    $child['children'][] = [
                        'title' => $page->title ?: $page->fileName,
                        'url'   => $url,
                    ];
                    $placed = true;
                    break;
                }
            }

            if (!$placed) {
                $verseChildren[] = [
                    'title' => $page->title ?: $page->fileName,
                    'url'   => $url,
                ];
            }

            $added++;
        }

        // Update the final structure
        $nav['main'][$verseIndex]['children'] = $verseChildren;

        // Write the new generated file
        $yaml = Yaml::dump($nav, 6, 2);
        File::put($targetPath, $yaml);

        return $added;
    }

    protected static function collectUrls($children)
    {
        $urls = [];
        foreach ($children as $child) {
            if (isset($child['url'])) $urls[] = $child['url'];
            if (isset($child['children'])) {
                $urls = array_merge($urls, self::collectUrls($child['children']));
            }
        }
        return $urls;
    }
    protected static function mergeNav(array $base, array $manual): array
    {
        foreach ($manual as $key => $value) {
            // If key exists and both are arrays → merge recursively
            if (isset($base[$key]) && is_array($value) && is_array($base[$key])) {
                $base[$key] = self::mergeNav($base[$key], $value);
            } else {
                // Manual entries override or extend base
                $base[$key] = $value;
            }
        }
        return $base;
    }
}


