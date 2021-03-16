<?php

declare(strict_types=1);

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Emoji\Tests\Unit\Event;

use PHPUnit\Framework\TestCase;

final class AbstractEventTest extends TestCase
{
    public function testStopPropagation(): void
    {
        $event = new FakeEvent();

        $this->assertFalse($event->isPropagationStopped());

        $event->stopPropagation();
        $this->assertTrue($event->isPropagationStopped());
    }
}
