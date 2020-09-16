const emojibase = require('emojibase')

const {
    EMOJI,
    FEMALE,
    FIRST_UNICODE_EMOJI_VERSION,
    LATEST_CLDR_VERSION,
    LATEST_EMOJI_VERSION,
    LATEST_UNICODE_VERSION,
    MALE,
    NON_LATIN_LOCALES,
    SUPPORTED_LOCALES,
    TEXT,
    UNICODE_VERSIONS,
} = emojibase

/**
 * Add an "auto" constant (for default emojibase behavior).
 */
const AUTO = null

/**
 * Add a "gender" constant.
 */
const GENDER = {}
GENDER['self::FEMALE'] = 'female'
GENDER['self::MALE'] = 'male'

/**
 * Add supported presentation modes.
 */
const SUPPORTED_PRESENTATIONS = [
    'self::AUTO',
    'self::TEXT',
    'self::EMOJI',
]

const emojiDataset = {
    AUTO,
    EMOJI,
    FEMALE,
    FIRST_UNICODE_EMOJI_VERSION,
    GENDER,
    LATEST_CLDR_VERSION,
    LATEST_EMOJI_VERSION,
    LATEST_UNICODE_VERSION,
    MALE,
    NON_LATIN_LOCALES,
    SUPPORTED_LOCALES,
    SUPPORTED_PRESENTATIONS,
    TEXT,
    UNICODE_VERSIONS,
};

module.exports = emojiDataset
