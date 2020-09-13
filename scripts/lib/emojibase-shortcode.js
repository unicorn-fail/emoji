const emojibaseShortcode = {};

/**
 * Add missing shortcode constants.
 *
 * @see https://emojibase.dev/docs/shortcodes
 */
emojibaseShortcode.PRESET_CLDR = 'cldr'
emojibaseShortcode.PRESET_CLDR_NATIVE = 'cldr-native'
emojibaseShortcode.PRESET_EMOJIBASE = 'emojibase'
emojibaseShortcode.PRESET_EMOJIBASE_LEGACY = 'emojibase-legacy'
emojibaseShortcode.PRESET_GITHUB = 'github'
emojibaseShortcode.PRESET_IAMCAL = 'iamcal'
emojibaseShortcode.PRESET_JOYPIXELS = 'joypixels'
emojibaseShortcode.PRESET_DISCORD = 'discord'
emojibaseShortcode.PRESET_SLACK = 'slack'

emojibaseShortcode.PRESETS = {}
emojibaseShortcode.PRESETS['self::PRESET_CLDR'] = 'self::PRESET_CLDR'
emojibaseShortcode.PRESETS['self::PRESET_CLDR_NATIVE'] = 'self::PRESET_CLDR_NATIVE'
emojibaseShortcode.PRESETS['self::PRESET_EMOJIBASE'] = 'self::PRESET_EMOJIBASE'
emojibaseShortcode.PRESETS['self::PRESET_EMOJIBASE_LEGACY'] = 'self::PRESET_EMOJIBASE_LEGACY'
emojibaseShortcode.PRESETS['self::PRESET_GITHUB'] = 'self::PRESET_GITHUB'
emojibaseShortcode.PRESETS['self::PRESET_IAMCAL'] = 'self::PRESET_IAMCAL'
emojibaseShortcode.PRESETS['self::PRESET_JOYPIXELS'] = 'self::PRESET_JOYPIXELS'

emojibaseShortcode.PRESET_ALIASES = {}
emojibaseShortcode.PRESET_ALIASES['self::PRESET_DISCORD'] = 'self::PRESET_JOYPIXELS'
emojibaseShortcode.PRESET_ALIASES['self::PRESET_SLACK'] = 'self::PRESET_IAMCAL'

emojibaseShortcode.DEFAULT_PRESETS = [
    'self::PRESET_EMOJIBASE',
    'self::PRESET_CLDR_NATIVE',
    'self::PRESET_CLDR'
]

emojibaseShortcode.SUPPORTED_PRESETS = [
    ...Object.keys(emojibaseShortcode.PRESETS),
    ...Object.keys(emojibaseShortcode.PRESET_ALIASES),
]

module.exports = emojibaseShortcode
