<?php

declare(strict_types=1);

namespace Swew\Test\Utils;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class Hash
{
    public static function fromFiles(array $files, string $algorithm = 'sha256'): string
    {
        $hashes = [];

        foreach ($files as $file) {
            $hashes[] = self::path(dirname($file, 2));
        }

        $combinedHash = implode('', $hashes);
        return hash($algorithm, $combinedHash);
    }

    private static function path(string $dirPath, string $algorithm = 'sha256'): string
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirPath, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $hashes = [];

        foreach ($files as $file) {
            if ($file->isFile()) {
                $filePath = $file->getRealPath();
                $fileHash = hash_file($algorithm, $filePath);
                $metadata = [
                    'name' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'mtime' => $file->getMTime(),
                    'hash' => $fileHash,
                ];
                $hashes[] = serialize($metadata);
            }
        }

        // Sort hashes to ensure consistent order
        sort($hashes);

        // Concatenate all hashes and compute the final hash
        $combinedHash = implode('', $hashes);
        return hash($algorithm, $combinedHash);
    }
}
