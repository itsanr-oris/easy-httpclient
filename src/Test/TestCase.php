<?php

namespace Foris\Easy\HttpClient\Test;

use Mockery;

/**
 * Class TestCase
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Tear down the test case.
     */
    public function tearDown(): void
    {
        if (class_exists('Mockery')) {
            if ($container = Mockery::getContainer()) {
                $this->addToAssertionCount($container->mockery_getExpectationCount());
            }

            Mockery::close();
        }
    }
}