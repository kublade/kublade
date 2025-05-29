<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers\Kubernetes;

use App\Helpers\Kubernetes\YamlFormatter;
use PHPUnit\Framework\TestCase;

/**
 * Class YamlFormatterTest.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class YamlFormatterTest extends TestCase
{
    /**
     * Test that YAML without '---' prefix gets formatted correctly.
     */
    public function testFormatAddsPrefixToYamlWithoutPrefix(): void
    {
        $input    = "apiVersion: v1\nkind: Pod";
        $expected = "---\napiVersion: v1\nkind: Pod";

        $result = YamlFormatter::format($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test that YAML with '---' prefix remains unchanged.
     */
    public function testFormatKeepsExistingPrefix(): void
    {
        $input    = "---\napiVersion: v1\nkind: Pod";
        $expected = "---\napiVersion: v1\nkind: Pod";

        $result = YamlFormatter::format($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test that empty string gets formatted correctly.
     */
    public function testFormatHandlesEmptyString(): void
    {
        $input    = '';
        $expected = "---\n";

        $result = YamlFormatter::format($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test that string with only whitespace gets formatted correctly.
     */
    public function testFormatHandlesWhitespaceOnlyString(): void
    {
        $input    = "   \n\t  ";
        $expected = "---\n   \n\t  ";

        $result = YamlFormatter::format($input);

        $this->assertEquals($expected, $result);
    }

    /**
     * Test that YAML with '---' prefix and whitespace before it gets formatted correctly.
     */
    public function testFormatHandlesYamlWithWhitespaceBeforePrefix(): void
    {
        $input    = "   ---\napiVersion: v1\nkind: Pod";
        $expected = "---\n   ---\napiVersion: v1\nkind: Pod";

        $result = YamlFormatter::format($input);

        $this->assertEquals($expected, $result);
    }
}
