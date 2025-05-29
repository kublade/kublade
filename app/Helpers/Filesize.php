<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Class Filesize.
 *
 * This class is the helper for handling filesizes.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class Filesize
{
    /**
     * Convert a value to bytes.
     *
     * @param string $value
     * @param string $unit
     *
     * @return float|int
     */
    public static function toBytes(string $value, string $unit = 'B'): int | float
    {
        $isBinary = str_contains($unit, 'i');
        $base     = $isBinary ? 1024 : 1000;

        // Remove 'i' from binary units for prefix detection
        $baseUnit = str_replace('i', '', $unit);
        $baseUnit = substr($baseUnit, 0, 1);

        switch ($baseUnit) {
            case 'B':
            default:
                $power = 0;

                break;
            case 'K':
            case 'k':
                $power = 1;

                break;
            case 'M':
            case 'm':
                $power = 2;

                break;
            case 'G':
            case 'g':
                $power = 3;

                break;
            case 'T':
            case 't':
                $power = 4;

                break;
            case 'P':
            case 'p':
                $power = 5;

                break;
            case 'E':
            case 'e':
                $power = 6;

                break;
            case 'Z':
            case 'z':
                $power = 7;

                break;
            case 'Y':
            case 'y':
                $power = 8;

                break;
        }

        if ($power === 0) {
            return (float) $value;
        }

        return (float) $value * pow($base, $power);
    }

    /**
     * Convert a string to bytes.
     *
     * @param string $str
     *
     * @return float|int
     */
    public static function bytesFromString(string $str): int | float
    {
        // Extract the numeric part and unit
        if (preg_match('/^([\d.]+)\s*([kmgtpezy]?i?b)?$/i', $str, $matches)) {
            $value = $matches[1];
            $unit  = strtoupper($matches[2] ?? 'B');

            // Special handling for binary units
            if (str_contains($unit, 'I')) {
                $unit = str_replace('I', 'i', $unit);
            }
        } else {
            // If no unit is found, assume bytes
            $value = $str;
            $unit  = 'B';
        }

        return self::toBytes($value, $unit);
    }
}
