<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Class TestCase.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}
