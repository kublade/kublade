<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use App\Helpers\Filesize;
use PHPUnit\Framework\TestCase;

/**
 * Class FilesizeTest.
 *
 * Unit tests for the Filesize helper class
 */
class FilesizeTest extends TestCase
{
    /**
     * Test conversion to bytes with different units.
     *
     * @dataProvider toBytesProvider
     *
     * @param string    $value
     * @param string    $unit
     * @param float|int $expected
     */
    public function testToBytes(string $value, string $unit, float | int $expected): void
    {
        $result = Filesize::toBytes($value, $unit);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test conversion from string to bytes.
     *
     * @dataProvider bytesFromStringProvider
     *
     * @param string    $input
     * @param float|int $expected
     */
    public function testBytesFromString(string $input, float | int $expected): void
    {
        $result = Filesize::bytesFromString($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test invalid unit handling.
     */
    public function testInvalidUnitHandling(): void
    {
        // Should default to bytes (B) for invalid units
        $this->assertEquals(42.0, Filesize::toBytes('42', 'X'));
    }

    /**
     * Test binary units (KiB, MiB, etc.).
     */
    public function testBinaryUnits(): void
    {
        $this->assertEquals(1024, Filesize::toBytes('1', 'KiB'));
        $this->assertEquals(1024 * 1024, Filesize::toBytes('1', 'MiB'));
        $this->assertEquals(1024 * 1024 * 1024, Filesize::toBytes('1', 'GiB'));
    }

    /**
     * Test handling of numbers without units.
     */
    public function testNumbersWithoutUnits(): void
    {
        // Test toBytes with default unit (B)
        $this->assertEquals(42.0, Filesize::toBytes('42'));      // Should default to bytes
        $this->assertEquals(1.5, Filesize::toBytes('1.5'));      // Should handle decimals
        $this->assertEquals(0.0, Filesize::toBytes('0'));        // Should handle zero
        $this->assertEquals(42.0, Filesize::toBytes('42', ''));  // Empty unit should default to bytes

        // Test bytesFromString with numbers only
        $this->assertEquals(42.0, Filesize::bytesFromString('42'));      // Should default to bytes
        $this->assertEquals(1.5, Filesize::bytesFromString('1.5'));      // Should handle decimals
        $this->assertEquals(0.0, Filesize::bytesFromString('0'));        // Should handle zero
        $this->assertEquals(42.0, Filesize::bytesFromString('42 '));     // Should handle trailing space
        $this->assertEquals(42.0, Filesize::bytesFromString(' 42'));     // Should handle leading space
        $this->assertEquals(42.0, Filesize::bytesFromString(' 42 '));    // Should handle both spaces
    }

    /**
     * Data provider for toBytes tests.
     */
    public static function toBytesProvider(): array
    {
        return [
            // Basic units
            ['1', 'B', 1.0],
            ['1', 'KB', 1000.0],
            ['1', 'MB', 1000000.0],
            ['1', 'GB', 1000000000.0],
            ['1', 'TB', 1000000000000.0],
            ['1', 'PB', 1000000000000000.0],
            ['1', 'EB', 1000000000000000000.0],
            ['1', 'ZB', 1000000000000000000000.0],
            ['1', 'YB', 1000000000000000000000000.0],

            // Decimal values
            ['1.5', 'KB', 1500.0],
            ['2.5', 'MB', 2500000.0],
            ['0.5', 'GB', 500000000.0],

            // Case insensitivity
            ['1', 'kb', 1000.0],
            ['1', 'Mb', 1000000.0],
            ['1', 'gb', 1000000000.0],

            // Zero values
            ['0', 'B', 0.0],
            ['0', 'KB', 0.0],
            ['0', 'MB', 0.0],
        ];
    }

    /**
     * Data provider for bytesFromString tests.
     */
    public static function bytesFromStringProvider(): array
    {
        return [
            // Basic formats
            ['1B', 1.0],
            ['1KB', 1000.0],
            ['1MB', 1000000.0],
            ['1GB', 1000000000.0],
            ['1TB', 1000000000000.0],
            ['1PB', 1000000000000000.0],
            ['1EB', 1000000000000000000.0],
            ['1ZB', 1000000000000000000000.0],
            ['1YB', 1000000000000000000000000.0],

            // Decimal values
            ['1.5KB', 1500.0],
            ['2.5MB', 2500000.0],
            ['0.5GB', 500000000.0],

            // Case insensitivity
            ['1kb', 1000.0],
            ['1Mb', 1000000.0],
            ['1gb', 1000000000.0],

            // Numbers without unit (defaults to bytes)
            ['42', 42.0],
            ['0', 0.0],
            ['1.5', 1.5],

            // Binary units
            ['1KiB', 1024.0],
            ['1MiB', 1024 * 1024.0],
            ['1GiB', 1024 * 1024 * 1024.0],
        ];
    }
}
