#!/usr/bin/env node

const emojibase = require('./lib/emojibase')
const emojibaseGroups = require('./lib/emojibase-groups')
const emojibaseRegex = require('./lib/emojibase-regex')
const emojibaseShortcode = require('./lib/emojibase-shortcode')
const emojibaseSkins = require('./lib/emojibase-skins')
const PhpInterface = require('./lib/php-interface')

PhpInterface.create('UnicornFail\\Emoji\\EmojibaseShortcodeInterface')
    .setModule('emojibase')
    .addConstants(emojibaseShortcode)
    .write()

PhpInterface.create('UnicornFail\\Emoji\\EmojibaseSkinsInterface')
    .setModule('emojibase')
    .addConstants(emojibaseSkins)
    .write()

PhpInterface.create('UnicornFail\\Emoji\\EmojibaseGroupsInterface')
    .setModule('emojibase')
    .addConstants(emojibaseGroups)
    .write()

PhpInterface.create('UnicornFail\\Emoji\\EmojibaseInterface')
    .setModule('emojibase')
    .addConstants(emojibase, [/^EMOTICON_OPTIONS$/, /^GROUP/, /SKIN/])
    .write()

PhpInterface.create('UnicornFail\\Emoji\\EmojibaseRegexInterface')
    .setModule('emojibase-regex')
    .addConstants(emojibaseRegex)
    .write()

console.log(`\nDone!\n`)
