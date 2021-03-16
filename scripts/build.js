#!/usr/bin/env node

const emojibaseDataset = require('./lib/emojibase-dataset')
const emojibaseGroups = require('./lib/emojibase-groups')
const emojibaseRegex = require('./lib/emojibase-regex')
const emojibaseShortcode = require('./lib/emojibase-shortcode')
const emojibaseSkins = require('./lib/emojibase-skins')
const PhpInterface = require('./lib/php-interface')

PhpInterface.create('League\\Emoji\\Emojibase\\EmojibaseDatasetInterface')
    .setModule('emojibase')
    .addConstants(emojibaseDataset)
    .write()

PhpInterface.create('League\\Emoji\\Emojibase\\EmojibaseShortcodeInterface')
    .setModule('emojibase')
    .addConstants(emojibaseShortcode)
    .write()

PhpInterface.create('League\\Emoji\\Emojibase\\EmojibaseSkinsInterface')
    .setModule('emojibase')
    .addConstants(emojibaseSkins)
    .write()

PhpInterface.create('League\\Emoji\\Emojibase\\EmojibaseGroupsInterface')
    .setModule('emojibase')
    .addConstants(emojibaseGroups)
    .write()

PhpInterface.create('League\\Emoji\\Emojibase\\EmojibaseRegexInterface')
    .setModule('emojibase-regex')
    .addConstants(emojibaseRegex)
    .write()

console.log(`\nDone!\n`)
