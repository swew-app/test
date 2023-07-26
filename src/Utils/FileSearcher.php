<?php

declare(strict_types=1);

namespace Swew\Test\Utils;

class FileSearcher
{
    public static function makeSubPathPatterns(array $paths): array
    {
        $added = [];
        // TODO: Заменить на поиск по файлам

        foreach ($paths as $path) {
            if (str_contains($path, '**')) {
                $added[] = str_replace('**', '', $path);
                $added[] = str_replace('**', '*', $path);
                $added[] = str_replace('**', '*/*', $path);
                $added[] = str_replace('**', '*/*/*', $path);
                $added[] = str_replace('**', '*/*/*/*', $path);
                $added[] = str_replace('**', '*/*/*/*/*', $path);
                $added[] = str_replace('**', '*/*/*/*/*/*', $path);
            }
        }

        return array_merge($paths, $added);
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
