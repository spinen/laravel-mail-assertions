<?php

namespace Spinen\MailAssertions;

use Mockery;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Class TestCase
 *
 * @package Tests\Spinen\BrowserFilter
 */
abstract class TestCase extends PHPUnitTestCase
{
    public function tearDown(): void
    {
        if (class_exists('Mockery')) {
            Mockery::close();
        }

        parent::tearDown();
    }
}
