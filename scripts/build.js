#!/usr/bin/env node

const emojibaseDataset = require('./lib/emojibase-dataset')
const emojibaseGroups = require('./lib/emojibase-groups')
const emojibaseRegex = require('./lib/emojibase-regex')
const emojibaseShortcode = require('./lib/emojibase-shortcode')
const emojibaseSkins = require('./lib/emojibase-skins')
const PhpInterface = require('./lib/php-interface')

PhpInterface.create('UnicornFail\\Emoji\\Emojibase\\DatasetInterface')
    .setModule('emojibase')
    .addConstants(emojibaseDataset)
    .write()

PhpInterface.create('UnicornFail\\Emoji\\Emojibase\\ShortcodeInterface')
    .setModule('emojibase')
    .addConstants(emojibaseShortcode)
    .write()

PhpInterface.create('UnicornFail\\Emoji\\Emojibase\\SkinsInterface')
    .setModule('emojibase')
    .addConstants(emojibaseSkins)
    .write()

PhpInterface.create('UnicornFail\\Emoji\\Emojibase\\GroupsInterface')
    .setModule('emojibase')
    .addConstants(emojibaseGroups)
    .write()

PhpInterface.create('UnicornFail\\Emoji\\Emojibase\\RegexInterface')
    .setModule('emojibase-regex')
    .addConstants(emojibaseRegex)
    .write()

console.log(`\nDone!\n`)
