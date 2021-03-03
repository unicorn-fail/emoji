<?php

declare(strict_types=1);

/*
 * This file was originally part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (https://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace UnicornFail\Emoji\Node\Inline;

use UnicornFail\Emoji\Node\Node;

abstract class AbstractInline extends Node
{
    /**
     * @var array<string, mixed>
     *
     * Used for storage of arbitrary data
     */
    public $data = [];

    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public function getData(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }
}
