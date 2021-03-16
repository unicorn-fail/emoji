<?php

declare(strict_types=1);

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (https://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Emoji\Renderer;

use League\Configuration\ConfigurationAwareInterface;
use League\Configuration\ConfigurationInterface;
use League\Emoji\Node\Image;
use League\Emoji\Node\Node;
use League\Emoji\Util\HtmlElement;

final class ImageRenderer implements NodeRendererInterface, ConfigurationAwareInterface
{
    public const REGEX_UNSAFE_PROTOCOL    = '/^javascript:|vbscript:|file:|data:/i';
    public const REGEX_SAFE_DATA_PROTOCOL = '/^data:image\/(?:png|gif|jpeg|webp)/i';

    /**
     * @var ConfigurationInterface
     *
     * @psalm-readonly-allow-private-mutation
     * @psalm-suppress PropertyNotSetInConstructor
     */
    private $config;

    /**
     * @param Node $node
     *
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function render(Node $node)
    {
        if (! ($node instanceof Image)) {
            throw new \InvalidArgumentException('Incompatible node type: ' . \get_class($node));
        }

        $allowUnsafeLinks = (bool) $this->config->get('allow_unsafe_links');
        if (! $allowUnsafeLinks && self::isLinkPotentiallyUnsafe($node->getUrl())) {
            $node->setAttribute('src', '');
        }

        return new HtmlElement('img', $node->getAttributes()->export(), '', true);
    }

    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->config = $configuration;
    }

    /**
     * @psalm-pure
     */
    public static function isLinkPotentiallyUnsafe(string $url): bool
    {
        return \preg_match(self::REGEX_UNSAFE_PROTOCOL, $url) !== 0 && \preg_match(self::REGEX_SAFE_DATA_PROTOCOL, $url) === 0;
    }
}
