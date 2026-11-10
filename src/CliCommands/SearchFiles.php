<?php

declare(strict_types=1);

namespace Swew\Test\CliCommands;

use LogicException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Swew\Cli\Args\Argument;
use Swew\Cli\Args\CliCommand;
use Swew\Cli\Args\CliCtx;
use Swew\Cli\CommandCtx;
use Swew\Test\TestMaster;

#[CliCommand(name: 'searchSuite', desc: 'Search and filtering')]
class SearchFiles
{
    #[CliCtx]
    private CommandCtx $ctx;

    #[Argument(aliases: ['f'], desc: 'Filter by file name')]
    private string $filter = '';

    #[Argument(aliases: ['sf'], desc: 'Filter by test title')]
    private string $suite = '';

    public function __invoke(): int
    {
        $matchingFiles = [];

        $commander = $this->ctx->getCommander();

        if (!$commander instanceof TestMaster) {
            throw new LogicException('Is not testMaster');
        }

        // Получаем паттерны по которым искать
        $directory = $commander->config->getRoot();
        $patterns = $commander->config->paths;

        if (count($patterns) === 0) {
            $this->ctx->output->error('Empty search patterns');
            exit(1);
        }

        // Делаем поиск файлов
        foreach ($patterns as $pattern) {
            $this->searchFilePath($directory, $pattern, $matchingFiles);
        }

        // Получаем аргументы для фильтрации списка fileName SuiteName
        $filter = $this->filter;

        // Если фильтр пуст, а последним аргументом передали название файла
        if (empty($filter)) {
            $args = array_slice($this->ctx->getArgs(), 1);

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
            $commander->config->setFilter($filter);
            $matchingFiles = $this->filterFiles($filter, $matchingFiles);
        }

        $suite = $this->suite;

        if (!empty($suite)) {
            $commander->config->setSuite($suite);
        }

        $commander->config->setTestFiles($matchingFiles);

        return 0;
    }

    /** @param array<string> $matchingFiles */
    private function searchFilePath(string $directory, string $pattern, array &$matchingFiles): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory),
            RecursiveIteratorIterator::SELF_FIRST
        );

        if ($pattern[0] !== '*') {
            $pattern = '*' . $pattern;
        }

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (
                $file->isFile()
                && !str_contains($file->getPathname(), 'vendor')
                && fnmatch($pattern, $file->getPathname())
            ) {
                $matchingFiles[] = $file->getPathname();
            }
        }
    }

    /**
     * @param array<string> $matchingFiles
     * @return array<string>
     */
    private function filterFiles(string $filter, array $matchingFiles): array
    {
        $result = @preg_grep("/$filter/", $matchingFiles);
        if ($result === false) {
            throw new LogicException("Invalid file filter: $filter");
        }

        return $result;
    }
}
