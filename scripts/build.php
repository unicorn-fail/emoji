#!/usr/bin/env php
<?php

declare(strict_types=1);

const BASE_DIRECTORY = __DIR__ . '/../';
require_once BASE_DIRECTORY . '/vendor/autoload.php';

use UnicornFail\Emoji\Dataset;
use UnicornFail\Emoji\EmojibaseInterface;
use UnicornFail\Emoji\EmojibaseShortcodeInterface;
use UnicornFail\Emoji\Util\Normalize;

const BUILD_DIRECTORY          = BASE_DIRECTORY . '/build';
const EMOJIBASE_DATA_DIRECTORY = BASE_DIRECTORY . '/node_modules/emojibase-data';

if (! is_dir(EMOJIBASE_DATA_DIRECTORY) || ! interface_exists(EmojibaseInterface::class)) {
    throw new \RuntimeException('You must first run `npm install && npm run build` to build the datasets.');
}

/**
 * @param mixed[] $shortcodes
 */
function create_dataset(string $locale, array $shortcodes): Dataset
{
    $data = null;

    // Load the data, key by hexcode.
    $file = sprintf('%s/%s/data.json', EMOJIBASE_DATA_DIRECTORY, $locale);
    if (file_exists($file) && ($c = file_get_contents($file)) && ($json = json_decode($c, true))) {
        $data = array_column($json, null, 'hexcode');
    }

    if (! isset($data)) {
        throw new \RuntimeException(sprintf('Unable to load JSON: %s', $file));
    }

    // Merge any skin variations into the main list (faster performance).
    foreach ($data as $hexcode => &$item) {
        if (! isset($item['shortcodes'])) {
            $item['shortcodes'] = [];
        }

        // Process shortcodes.
        $item += ['shortcodes' => []];
        if (isset($shortcodes[$hexcode])) {
            $item['shortcodes'] = Normalize::shortcodes($item['shortcodes'], $shortcodes[$hexcode]);
        }

        if (! isset($item['skins']) || ! count($item['skins'])) {
            continue;
        }

        $item['skins'] = array_column($item['skins'], null, 'hexcode');

        foreach ($item['skins'] as $skinHexcode => &$skin) {
            if (isset($data[$skinHexcode])) {
                continue;
            }

            // Process shortcodes.
            $skin += ['shortcodes' => []];
            if (isset($shortcodes[$skinHexcode])) {
                $skin['shortcodes'] = Normalize::shortcodes($skin['shortcodes'], $shortcodes[$skinHexcode]);
            }
        }
    }

    return new Dataset($data);
}

// Clean up dataset directory.
if (is_dir(Dataset::DIRECTORY)) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(Dataset::DIRECTORY, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $fileInfo) {
        $todo = ($fileInfo->isDir() ? 'rmdir' : 'unlink');
        $todo($fileInfo->getRealPath());
    }

    /** @scrutinizer ignore-unhandled */ @rmdir(Dataset::DIRECTORY);
}

$baseDirectory = realpath(BASE_DIRECTORY);

// Archive datasets.
foreach (EmojibaseInterface::SUPPORTED_LOCALES as $locale) {
    foreach (EmojibaseShortcodeInterface::PRESETS as $preset) {
        // Skip presets that don't exist.
        $file = sprintf('%s/%s/shortcodes/%s.json', EMOJIBASE_DATA_DIRECTORY, $locale, $preset);
        if (! file_exists($file) || ! ($c = file_get_contents($file)) || ! ($shortcodes = json_decode($c, true))) {
            continue;
        }

        $destination = sprintf('%s/%s/%s.gz', Dataset::DIRECTORY, $locale, $preset);
        $relative    = str_replace($baseDirectory . '/src/..', '.', $destination);
        $directory   = dirname($destination);
        /** @scrutinizer ignore-unhandled */ @mkdir($directory, 0775, true);

        echo sprintf("Archiving %s\n", $relative);
        $dataset = create_dataset($locale, $shortcodes);
        file_put_contents($destination, $dataset->archive());
    }
}

echo "\nFinished!\n";
