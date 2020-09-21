<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Environment;

use UnicornFail\Emoji\Dataset\DatasetInterface;
use UnicornFail\Emoji\Parser\ParserInterface;

interface EmojiEnvironmentInterface extends ConfigurableEnvironmentInterface
{
    public function getDataset(): DatasetInterface;

    public function getParser(): ParserInterface;

    public function setDataset(DatasetInterface $dataset): void;

    public function setParser(ParserInterface $parser): void;
}
