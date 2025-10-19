<?php namespace Dataverse\Core\Classes;

use Cms\Classes\Page;
use Symfony\Component\Yaml\Yaml;
use File;
use Illuminate\Support\Str;

class NavBuilder
{
    public static function rebuild()
    {
        $path = themes_path('dataverse/meta/navigation.yaml');
        $existing = File::exists($path)
            ? Yaml::parseFile($path)
            : ['main' => []];

        // Locate "The Verse"
        $verseIndex = null;
        foreach ($existing['main'] as $i => $item) {
            if (isset($item['title']) && strtolower($item['title']) === 'the verse') {
                $verseIndex = $i;
                break;
            }
        }

        // Create "The Verse" if missing
        if ($verseIndex === null) {
            $existing['main'][] = ['title' => 'The Verse', 'children' => []];
            $verseIndex = count($existing['main']) - 1;
        }

        $verseChildren = $existing['main'][$verseIndex]['children'] ?? [];

        // Recursive helper: collect all existing URLs
        $collectUrls = function ($children) use (&$collectUrls) {
            $urls = [];
            foreach ($children as $child) {
                if (isset($child['url'])) $urls[] = $child['url'];
                if (isset($child['children'])) {
                    $urls = array_merge($urls, $collectUrls($child['children']));
                }
            }
            return $urls;
        };
        $existingUrls = $collectUrls($verseChildren);

        $pages = Page::all();
        $added = 0;

        foreach ($pages as $page) {
            if (property_exists($page, 'hidden') && $page->hidden) continue;

            $url = $page->url;

            // Skip system / utility pages
            if (
                !$url ||
                $url === '/' ||
                Str::contains($url, '404') ||
                Str::startsWith($page->fileName, '_')
            ) continue;

            // Skip duplicates
            if (in_array($url, $existingUrls)) continue;

            // Try to find a parent section based on URL prefix (e.g. /minecraft/)
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

            // No match → append to bottom of The Verse
            if (!$placed) {
                $verseChildren[] = [
                    'title' => $page->title ?: $page->fileName,
                    'url'   => $url,
                ];
            }

            $added++;
        }

        // Update the structure
        $existing['main'][$verseIndex]['children'] = $verseChildren;

        // Write back to YAML
        $yaml = Yaml::dump($existing, 4);
        File::put($path, $yaml);

        return $added;
    }
}
