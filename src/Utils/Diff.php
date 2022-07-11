<?php

declare(strict_types=1);

namespace SWEW\Test\Utils;

use DateTime;
use DateTimeImmutable;

final class Diff
{

    public static function diff(mixed $v1, mixed $v2): string
    {
        $s1 = self::valueToString($v1);
        $s2 = self::valueToString($v2);

        if ($s1 === $s2) {
            return '';
        }

        $letters1 = str_split($s1);
        $letters2 = str_split($s2);

        $res = [];

        $indexes = array_keys(array_diff($letters1, $letters2));
        $res[] = self::highlighter($letters1, $indexes, false);

        $indexes = array_keys(array_diff($letters2, $letters1));
        $res[] = self::highlighter($letters2, $indexes, true);

        return implode("\n", $res);
    }

    public static function highlighter(array &$letters, array &$indexes, bool $isGood): string
    {
        $res = [];

        foreach ($letters as $i => $v) {
            if (in_array($i, $indexes)) {
                if ($isGood) {
                    $res[] = CliStr::cl('Ug', $v);
                } else {
                    $res[] = CliStr::cl('Ur', $v);
                }
            } else {
                $res[] = $v;
            }
        }

        return implode('', $res);
    }

    protected static function valueToString($value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_null($value)) {
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
                return \get_class($value) . ': ' . self::valueToString($value->__toString());
            }

            if ($value instanceof DateTime || $value instanceof DateTimeImmutable) {
                return \get_class($value) . ': ' . self::valueToString($value->format('c'));
            }

            return \get_class($value);
        }

        if (\is_resource($value)) {
            return 'resource';
        }

        if (\is_string($value)) {
            return '"' . $value . '"';
        }

        return (string)$value;
    }
}
