<?php

/*
 * This file was originally part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace UnicornFail\Emoji\Output;

use UnicornFail\Emoji\Node\Document;

interface RenderedContentInterface extends \Stringable
{
    /**
     * @psalm-mutation-free
     */
    public function getContent(): string;

    /**
     * @psalm-mutation-free
     */
    public function getDocument(): Document;
}
