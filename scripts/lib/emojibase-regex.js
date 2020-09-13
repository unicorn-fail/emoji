const CODEPOINT_EMOJI_LOOSE_REGEX = require('emojibase-regex/codepoint/emoji-loose')
const CODEPOINT_EMOJI_ONLY_REGEX = require('emojibase-regex/codepoint/emoji')
const CODEPOINT_EMOJI_REGEX = require('emojibase-regex/codepoint')
const CODEPOINT_TEXT_LOOSE_REGEX = require('emojibase-regex/codepoint/text-loose')
const CODEPOINT_TEXT_REGEX = require('emojibase-regex/codepoint/text')
const EMOJI_LOOSE_REGEX = require('emojibase-regex/emoji-loose')
const EMOJI_ONLY_REGEX = require('emojibase-regex/emoji')
const EMOJI_REGEX = require('emojibase-regex')
const EMOTICON_REGEX = require('emojibase-regex/emoticon')
const SHORTCODE_NATIVE_REGEX = require('emojibase-regex/shortcode-native')
const SHORTCODE_REGEX = require('emojibase-regex/shortcode')
const TEXT_LOOSE_REGEX = require('emojibase-regex/text-loose')
const TEXT_REGEX = require('emojibase-regex/text')

/**
 * Add missing HTML Entity regex.
 */
const HTML_ENTITY_REGEX = /&#x?[a-zA-Z0-9]*?;/

const emojibaseRegex = {
  CODEPOINT_EMOJI_LOOSE_REGEX,
  CODEPOINT_EMOJI_ONLY_REGEX,
  CODEPOINT_EMOJI_REGEX,
  CODEPOINT_TEXT_LOOSE_REGEX,
  CODEPOINT_TEXT_REGEX,
  EMOJI_LOOSE_REGEX,
  EMOJI_ONLY_REGEX,
  EMOJI_REGEX,
  EMOTICON_REGEX,
  HTML_ENTITY_REGEX,
  SHORTCODE_NATIVE_REGEX,
  SHORTCODE_REGEX,
  TEXT_LOOSE_REGEX,
  TEXT_REGEX,
}

module.exports = emojibaseRegex
