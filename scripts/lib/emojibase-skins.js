const emojibase = require('emojibase')

const emojibaseSkins = {}

Object.getOwnPropertyNames(emojibase).sort().forEach((name) => {
    if (/SKIN/.test(name)) {
        emojibaseSkins[name] = emojibase[name]
    }
})

/**
 * Add missing skin tone constants.
 */
emojibaseSkins.SKIN_TONES = {}
emojibaseSkins.SKIN_TONES['self::LIGHT_SKIN'] = 'self::SKIN_KEY_LIGHT'
emojibaseSkins.SKIN_TONES['self::MEDIUM_LIGHT_SKIN'] = 'self::SKIN_KEY_MEDIUM_LIGHT'
emojibaseSkins.SKIN_TONES['self::MEDIUM_SKIN'] = 'self::SKIN_KEY_MEDIUM'
emojibaseSkins.SKIN_TONES['self::MEDIUM_DARK_SKIN'] = 'self::SKIN_KEY_MEDIUM_DARK'
emojibaseSkins.SKIN_TONES['self::DARK_SKIN'] = 'self::SKIN_KEY_DARK'

module.exports = emojibaseSkins
