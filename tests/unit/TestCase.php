<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Tests\Unit;

use PHPUnit\Framework\TestCase as PhpUnitTestCase;

class TestCase extends PhpUnitTestCase
{
    public function getDataSetAsString(bool $includeData = false): string
    {
        return parent::getDataSetAsString($includeData);
    }
}
