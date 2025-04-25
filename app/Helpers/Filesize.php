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
     * @return int|float
     */
    public static function toBytes(string $value, string $unit = 'B'): int|float
    {
        $devideBy = substr($unit, -1) === 'i' ? 1024 : 1000;
        $baseUnit = substr($unit, 0, 1);

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
            return $value;
        }

        return $value * pow(1024, $power);
    }

    /**
     * Convert a string to bytes.
     *
     * @param string $str
     *
     * @return int|float
     */
    public static function bytesFromString(string $str): int|float
    {
        $format = substr($str, -2);

        if (!is_numeric($format)) {
            $value = substr($str, 0, -2);
        } else {
            $value  = $str;
            $format = 'B';
        }

        return self::toBytes($value, $format);
    }
}
