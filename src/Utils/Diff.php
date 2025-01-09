<?php

declare(strict_types=1);

namespace Swew\Test\Utils;

use DateTime;
use DateTimeImmutable;

final class Diff
{
    public static function diff(mixed $v1, mixed $v2, bool $isShowTitle = true): string
    {
        $s1 = self::valueToString($v1);
        $s2 = self::valueToString($v2);

        if ($s1 === $s2) {
            return '';
        }

        if ($s2 === '') {
            return '<yellow> value:</>'.PHP_EOL.CliStr::vm()->getWithPrefix($s1, false);
        }

        return CliStr::vm()->output->format(
            PHP_EOL . '<yellow>compare:</>' .PHP_EOL.PHP_EOL . self::compare($s1, $s2)
        );
    }

    private static function compare(string $first, string $second): string
    {
        $firstLines = explode("\n", $first);
        $secondLines = explode("\n", $second);

        $result = [];
        $maxLines = max(count($firstLines), count($secondLines));

        $prefix = '<bgGray>❯</>';

        for ($i = 0; $i < $maxLines; $i++) {
            $firstLine = $firstLines[$i] ?? '';
            $secondLine = $secondLines[$i] ?? '';

            if ($firstLine === $secondLine) {
                $result[] = $firstLine;
                continue;
            }

            if (strlen($secondLine) > strlen($firstLine)) {
                // Extra characters in second line
                $result[] = $prefix . $firstLine . "\n$prefix<red>" . $secondLine . '</>';
            } else {
                // Missing characters in second line
                $result[] = $prefix . '<red>' . $firstLine . "</>\n$prefix" . $secondLine;
            }
        }

        return implode("\n", $result);
    }

    private static function valueToString(mixed $value): string
    {
        if (\is_string($value)) {
            return $value;
        }

        if (null === $value) {
            return 'null';
        }

        if (true === $value) {
            return 'true';
        }

        if (false === $value) {
            return 'false';
        }

        if (\is_array($value)) {
            return var_export($value, true) ?? 'array';
        }

        if (\is_object($value)) {
            if (\method_exists($value, '__toString')) {
                return \get_class($value).': '.self::valueToString($value->__toString());
            }

            if ($value instanceof DateTime || $value instanceof DateTimeImmutable) {
                return \get_class($value).': '.self::valueToString($value->format('c'));
            }

            return \get_class($value);
        }

        if (\is_resource($value)) {
            return 'resource';
        }

        return (string) $value;
    }
}
