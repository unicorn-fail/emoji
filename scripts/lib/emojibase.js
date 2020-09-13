const emojibase = require('emojibase')

/**
 * Add an "auto" constant (for default emojibase behavior).
 */
emojibase.AUTO = null

/**
 * Add a "gender" constant.
 */
emojibase.GENDER = {}
emojibase.GENDER['self::FEMALE'] = 'female'
emojibase.GENDER['self::MALE'] = 'male'

/**
 * Add supported presentation modes.
 */
emojibase.SUPPORTED_PRESENTATIONS = [
    'self::AUTO',
    'self::TEXT',
    'self::EMOJI',
]

module.exports = emojibase
