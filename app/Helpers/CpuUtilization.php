<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Class CpuUtilization.
 *
 * This class is the helper for cpu utilization.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class CpuUtilization
{
    /**
     * Convert a value to cores.
     *
     * @param string $value
     *
     * @return int|float
     */
    public static function toCore(string $value): int|float
    {
        $baseUnit = substr($value, -1);

        if (is_numeric($baseUnit)) {
            return $value;
        }

        switch ($baseUnit) {
            case '':
            default:
                $devideBy = 0;

                break;
            case 'n':
                $devideBy = 1000000000;

                break;
            case 'm':
                $devideBy = 1000;

                break;
        }

        if ($devideBy === 0) {
            return $value;
        }

        return substr($value, 0, -1) / $devideBy;
    }
}
