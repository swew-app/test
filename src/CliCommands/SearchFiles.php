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

        if (! ($commander instanceof TestMaster)) {
            throw new LogicException('Is not testMaster');
        }

        // Получаем паттерны по которым искать
        $directory = $commander->config->getRoot();
        $patterns = $commander->config->paths;

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

        // Если фильтр пуст, а последним аргументом передали название файла
        if (empty($filter)) {
            $args = $this->getArgs();

            if (count($args) === 1) {
                // Проверяем, что последний аргумент подходит
                $namePattern = $args[0];
                // Название не начинается на `-`
                if ($namePattern[0] !== '-') {
                    $filter = $namePattern;
                }
            }
        }

        // Фильтруем список файлов относительно аргументов
        if ($filter) {
            if (gettype($filter) !== 'string') {
                throw new LogicException('Passed wrong type');
            }
            $commander->config->setFilter($filter);
            $matchingFiles = $this->filterFiles($filter, $matchingFiles);
        }

        $suite = $this->argv('suite');

        if (! empty($suite)) {
            if (gettype($suite) !== 'string') {
                throw new LogicException('Passed wrong type');
            }
            $commander->config->setSuite($suite);
        }

        $commander->config->setTestFiles($matchingFiles);

        return self::SUCCESS;
    }

    private function searchFilePath(string $directory, string $pattern, array &$matchingFiles): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory),
            RecursiveIteratorIterator::SELF_FIRST
        );

        if ($pattern[0] !== '*') {
            $pattern = '*'.$pattern;
        }

        foreach ($iterator as $file) {
            if (
                $file->isFile()
                && ! str_contains($file->getPathname(), 'vendor')
                && fnmatch($pattern, $file->getPathname())
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
