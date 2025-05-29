<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use App\Helpers\CpuUtilization;
use PHPUnit\Framework\TestCase;

/**
 * Class CpuUtilizationTest.
 *
 * Unit tests for the CpuUtilization helper class
 */
class CpuUtilizationTest extends TestCase
{
    /**
     * Test conversion to cores with different units.
     *
     * @dataProvider toCoreProvider
     *
     * @param string    $input
     * @param float|int $expected
     */
    public function testToCore(string $input, float | int $expected): void
    {
        $result = CpuUtilization::toCore($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test handling of invalid or edge cases.
     */
    public function testEdgeCases(): void
    {
        // Test empty string
        $this->assertEquals(0.0, CpuUtilization::toCore(''));

        // Test invalid unit (should default to core value)
        $this->assertEquals(42.0, CpuUtilization::toCore('42x'));

        // Test very small values
        $this->assertEquals(0.000000001, CpuUtilization::toCore('1n')); // 1 nano core
        $this->assertEquals(0.000000002, CpuUtilization::toCore('2n')); // 2 nano cores

        // Test very large values
        $this->assertEquals(1000.0, CpuUtilization::toCore('1000'));    // 1000 cores
        $this->assertEquals(1.0, CpuUtilization::toCore('1000m'));      // 1000 milli cores = 1 core
    }

    /**
     * Test decimal values with different units.
     */
    public function testDecimalValues(): void
    {
        // Test decimal cores
        $this->assertEquals(1.5, CpuUtilization::toCore('1.5'));        // 1.5 cores
        $this->assertEquals(0.5, CpuUtilization::toCore('0.5'));        // 0.5 cores

        // Test decimal milli cores
        $this->assertEquals(0.0015, CpuUtilization::toCore('1.5m'));    // 1.5 milli cores
        $this->assertEquals(0.0005, CpuUtilization::toCore('0.5m'));    // 0.5 milli cores

        // Test decimal nano cores
        $this->assertEquals(0.0000000015, CpuUtilization::toCore('1.5n')); // 1.5 nano cores
        $this->assertEquals(0.0000000005, CpuUtilization::toCore('0.5n')); // 0.5 nano cores
    }

    /**
     * Data provider for toCore tests.
     */
    public static function toCoreProvider(): array
    {
        return [
            // Core values (no unit)
            ['1', 1.0],
            ['2', 2.0],
            ['0', 0.0],
            ['1000', 1000.0],

            // Milli cores
            ['1000m', 1.0],      // 1000 milli cores = 1 core
            ['500m', 0.5],       // 500 milli cores = 0.5 cores
            ['100m', 0.1],       // 100 milli cores = 0.1 cores
            ['1m', 0.001],       // 1 milli core = 0.001 cores
            ['0m', 0.0],         // 0 milli cores = 0 cores

            // Nano cores
            ['1000000000n', 1.0],    // 1000000000 nano cores = 1 core
            ['500000000n', 0.5],     // 500000000 nano cores = 0.5 cores
            ['100000000n', 0.1],     // 100000000 nano cores = 0.1 cores
            ['1000000n', 0.001],     // 1000000 nano cores = 0.001 cores
            ['1000n', 0.000001],     // 1000 nano cores = 0.000001 cores
            ['1n', 0.000000001],     // 1 nano core = 0.000000001 cores
            ['0n', 0.0],             // 0 nano cores = 0 cores

            // Edge cases
            ['', 0.0],               // Empty string
            ['0.0', 0.0],            // Zero with decimal
            ['0.00', 0.0],           // Zero with multiple decimals
            ['1.0', 1.0],            // One with decimal
            ['1.00', 1.0],           // One with multiple decimals
        ];
    }
}
