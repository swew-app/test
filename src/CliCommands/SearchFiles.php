<?php

declare(strict_types=1);

namespace Swew\Test\CliCommands;

use LogicException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Swew\Cli\Command;
use Swew\Test\TestMaster;

class SearchFiles extends Command
{
    public const NAME = 'searchSuite
        {--filter|-f= (str): Filter by file name}
        {--suite|-sf= (str): Filter by test title}
        ';

    public const DESCRIPTION = 'Search and filtering';

    public function __invoke(): int
    {
        $matchingFiles = [];

        $commander = $this->getCommander();

        if (!($commander instanceof TestMaster)) {
            throw new LogicException('Is not testMaster');
        }

        // Получаем паттерны по которым искать
        $directory = realpath($commander->config['_root']);
        $patterns = $commander->config['paths'];

        if (count($patterns) === 0) {
            $this->output?->error('Empty search patterns');
            exit(self::ERROR);
        }

        // Делаем поиск файлов
        foreach ($patterns as $pattern) {
            $this->searchFilePath($directory, $pattern, $matchingFiles);
        }

        // Получаем аргументы для фильтрации списка fileName SuiteName
        $filter = $this->argv('filter');

        // Фильтруем список файлов относительно аргументов
        if ($filter) {
            $matchingFiles = $this->filterFiles($filter, $matchingFiles);
        }

        $suite = $this->argv('suite');

        if (!empty($suite)) {
            $commander->config['_suite'] = $suite;
        }

        $commander->config['_testFiles'] = $matchingFiles;

        return self::SUCCESS;
    }

    private function searchFilePath(string $directory, string $pattern, array &$matchingFiles): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if (
                $file->isFile()
                && !str_contains($file->getFilename(), 'vendor')
                && fnmatch($pattern, $file->getFilename())
            ) {
                $matchingFiles[] = $file->getPathname();
            }
        }
    }

    private function filterFiles(string $filter, array $matchingFiles): array
    {
        return preg_grep("/$filter/", $matchingFiles);
    }
}
