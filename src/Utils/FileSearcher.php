<?php

declare(strict_types=1);

namespace Swew\Test\Utils;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class FileSearcher
{
    public static function glob(array $patterns, string $directory): array
    {
        $matchingFiles = [];

        $directory = realpath($directory);

        foreach ($patterns as $pattern) {
            self::globPath($directory, $pattern, $matchingFiles);
        }

        return $matchingFiles;
    }

    private static function globPath(string $directory, string $pattern, array &$matchingFiles): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && fnmatch($pattern, $file->getFilename())) {
                $matchingFiles[] = $file->getPathname();
            }
        }
    }



    public static function getTestFilePaths(array $pathPatterns, string $filter = ''): array
    {
        $files = [];

        foreach ($pathPatterns as $path) {
            $files = array_merge($files, glob($path, GLOB_ERR));
        }

        // filter vendor
        $files = array_filter($files, function (string $path) {
            return !str_contains($path, 'vendor');
        });

        $testFiles = array_unique($files);

        if (empty($filter)) {
            return $testFiles;
        }

        $filter = str_replace('/', '\\/', $filter);
        return preg_grep("/$filter/", $testFiles) ?: [];
    }
}
