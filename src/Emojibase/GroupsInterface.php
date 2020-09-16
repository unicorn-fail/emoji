<?php

declare(strict_types=1);

namespace UnicornFail\Emoji\Emojibase;

/*!
 * IMPORTANT NOTE!
 *
 * THIS FILE IS BASED ON EXTRACTED DATA FROM THE NPM MODULE:
 *
 *     https://www.npmjs.com/package/emojibase
 *
 * DO NOT ATTEMPT TO DIRECTLY MODIFY THIS FILE. ALL MANUAL CHANGES MADE TO THIS FILE
 * WILL BE DESTROYED AUTOMATICALLY THE NEXT TIME IT IS REBUILT.
 */
interface GroupsInterface
{
    public const GROUPS = [
        self::GROUP_KEY_SMILEYS_EMOTION,
        self::GROUP_KEY_PEOPLE_BODY,
        self::GROUP_KEY_COMPONENT,
        self::GROUP_KEY_ANIMALS_NATURE,
        self::GROUP_KEY_FOOD_DRINK,
        self::GROUP_KEY_TRAVEL_PLACES,
        self::GROUP_KEY_ACTIVITIES,
        self::GROUP_KEY_OBJECTS,
        self::GROUP_KEY_SYMBOLS,
        self::GROUP_KEY_FLAGS,
    ];

    public const GROUP_KEY_ACTIVITIES = 'activities';

    public const GROUP_KEY_ANIMALS_NATURE = 'animals-nature';

    public const GROUP_KEY_COMPONENT = 'component';

    public const GROUP_KEY_FLAGS = 'flags';

    public const GROUP_KEY_FOOD_DRINK = 'food-drink';

    public const GROUP_KEY_OBJECTS = 'objects';

    public const GROUP_KEY_PEOPLE_BODY = 'people-body';

    public const GROUP_KEY_SMILEYS_EMOTION = 'smileys-emotion';

    public const GROUP_KEY_SYMBOLS = 'symbols';

    public const GROUP_KEY_TRAVEL_PLACES = 'travel-places';

    public const SUBGROUPS = [
        self::SUBGROUP_KEY_FACE_SMILING,
        self::SUBGROUP_KEY_FACE_AFFECTION,
        self::SUBGROUP_KEY_FACE_TONGUE,
        self::SUBGROUP_KEY_FACE_HAND,
        self::SUBGROUP_KEY_FACE_NEUTRAL_SKEPTICAL,
        self::SUBGROUP_KEY_FACE_SLEEPY,
        self::SUBGROUP_KEY_FACE_UNWELL,
        self::SUBGROUP_KEY_FACE_HAT,
        self::SUBGROUP_KEY_FACE_GLASSES,
        self::SUBGROUP_KEY_FACE_CONCERNED,
        self::SUBGROUP_KEY_FACE_NEGATIVE,
        self::SUBGROUP_KEY_FACE_COSTUME,
        self::SUBGROUP_KEY_CAT_FACE,
        self::SUBGROUP_KEY_MONKEY_FACE,
        self::SUBGROUP_KEY_EMOTION,
        self::SUBGROUP_KEY_HAND_FINGERS_OPEN,
        self::SUBGROUP_KEY_HAND_FINGERS_PARTIAL,
        self::SUBGROUP_KEY_HAND_SINGLE_FINGER,
        self::SUBGROUP_KEY_HAND_FINGERS_CLOSED,
        self::SUBGROUP_KEY_HANDS,
        self::SUBGROUP_KEY_HAND_PROP,
        self::SUBGROUP_KEY_BODY_PARTS,
        self::SUBGROUP_KEY_PERSON,
        self::SUBGROUP_KEY_PERSON_GESTURE,
        self::SUBGROUP_KEY_PERSON_ROLE,
        self::SUBGROUP_KEY_PERSON_FANTASY,
        self::SUBGROUP_KEY_PERSON_ACTIVITY,
        self::SUBGROUP_KEY_PERSON_SPORT,
        self::SUBGROUP_KEY_PERSON_RESTING,
        self::SUBGROUP_KEY_FAMILY,
        self::SUBGROUP_KEY_PERSON_SYMBOL,
        self::SUBGROUP_KEY_SKIN_TONE,
        self::SUBGROUP_KEY_HAIR_STYLE,
        self::SUBGROUP_KEY_ANIMAL_MAMMAL,
        self::SUBGROUP_KEY_ANIMAL_BIRD,
        self::SUBGROUP_KEY_ANIMAL_AMPHIBIAN,
        self::SUBGROUP_KEY_ANIMAL_REPTILE,
        self::SUBGROUP_KEY_ANIMAL_MARINE,
        self::SUBGROUP_KEY_ANIMAL_BUG,
        self::SUBGROUP_KEY_PLANT_FLOWER,
        self::SUBGROUP_KEY_PLANT_OTHER,
        self::SUBGROUP_KEY_FOOD_FRUIT,
        self::SUBGROUP_KEY_FOOD_VEGETABLE,
        self::SUBGROUP_KEY_FOOD_PREPARED,
        self::SUBGROUP_KEY_FOOD_ASIAN,
        self::SUBGROUP_KEY_FOOD_MARINE,
        self::SUBGROUP_KEY_FOOD_SWEET,
        self::SUBGROUP_KEY_DRINK,
        self::SUBGROUP_KEY_DISHWARE,
        self::SUBGROUP_KEY_PLACE_MAP,
        self::SUBGROUP_KEY_PLACE_GEOGRAPHIC,
        self::SUBGROUP_KEY_PLACE_BUILDING,
        self::SUBGROUP_KEY_PLACE_RELIGIOUS,
        self::SUBGROUP_KEY_PLACE_OTHER,
        self::SUBGROUP_KEY_TRANSPORT_GROUND,
        self::SUBGROUP_KEY_TRANSPORT_WATER,
        self::SUBGROUP_KEY_TRANSPORT_AIR,
        self::SUBGROUP_KEY_HOTEL,
        self::SUBGROUP_KEY_TIME,
        self::SUBGROUP_KEY_SKY_WEATHER,
        self::SUBGROUP_KEY_EVENT,
        self::SUBGROUP_KEY_AWARD_MEDAL,
        self::SUBGROUP_KEY_SPORT,
        self::SUBGROUP_KEY_GAME,
        self::SUBGROUP_KEY_ARTS_CRAFTS,
        self::SUBGROUP_KEY_CLOTHING,
        self::SUBGROUP_KEY_SOUND,
        self::SUBGROUP_KEY_MUSIC,
        self::SUBGROUP_KEY_MUSICAL_INSTRUMENT,
        self::SUBGROUP_KEY_PHONE,
        self::SUBGROUP_KEY_COMPUTER,
        self::SUBGROUP_KEY_LIGHT_VIDEO,
        self::SUBGROUP_KEY_BOOK_PAPER,
        self::SUBGROUP_KEY_MONEY,
        self::SUBGROUP_KEY_MAIL,
        self::SUBGROUP_KEY_WRITING,
        self::SUBGROUP_KEY_OFFICE,
        self::SUBGROUP_KEY_LOCK,
        self::SUBGROUP_KEY_TOOL,
        self::SUBGROUP_KEY_SCIENCE,
        self::SUBGROUP_KEY_MEDICAL,
        self::SUBGROUP_KEY_HOUSEHOLD,
        self::SUBGROUP_KEY_OTHER_OBJECT,
        self::SUBGROUP_KEY_TRANSPORT_SIGN,
        self::SUBGROUP_KEY_WARNING,
        self::SUBGROUP_KEY_ARROW,
        self::SUBGROUP_KEY_RELIGION,
        self::SUBGROUP_KEY_ZODIAC,
        self::SUBGROUP_KEY_AV_SYMBOL,
        self::SUBGROUP_KEY_GENDER,
        self::SUBGROUP_KEY_MATH,
        self::SUBGROUP_KEY_PUNCTUATION,
        self::SUBGROUP_KEY_CURRENCY,
        self::SUBGROUP_KEY_OTHER_SYMBOL,
        self::SUBGROUP_KEY_KEYCAP,
        self::SUBGROUP_KEY_ALPHANUM,
        self::SUBGROUP_KEY_GEOMETRIC,
        self::SUBGROUP_KEY_FLAG,
        self::SUBGROUP_KEY_COUNTRY_FLAG,
        self::SUBGROUP_KEY_SUBDIVISION_FLAG,
    ];

    public const SUBGROUP_KEY_ALPHANUM = 'alphanum';

    public const SUBGROUP_KEY_ANIMAL_AMPHIBIAN = 'animal-amphibian';

    public const SUBGROUP_KEY_ANIMAL_BIRD = 'animal-bird';

    public const SUBGROUP_KEY_ANIMAL_BUG = 'animal-bug';

    public const SUBGROUP_KEY_ANIMAL_MAMMAL = 'animal-mammal';

    public const SUBGROUP_KEY_ANIMAL_MARINE = 'animal-marine';

    public const SUBGROUP_KEY_ANIMAL_REPTILE = 'animal-reptile';

    public const SUBGROUP_KEY_ARROW = 'arrow';

    public const SUBGROUP_KEY_ARTS_CRAFTS = 'arts-crafts';

    public const SUBGROUP_KEY_AV_SYMBOL = 'av-symbol';

    public const SUBGROUP_KEY_AWARD_MEDAL = 'award-medal';

    public const SUBGROUP_KEY_BODY_PARTS = 'body-parts';

    public const SUBGROUP_KEY_BOOK_PAPER = 'book-paper';

    public const SUBGROUP_KEY_CAT_FACE = 'cat-face';

    public const SUBGROUP_KEY_CLOTHING = 'clothing';

    public const SUBGROUP_KEY_COMPUTER = 'computer';

    public const SUBGROUP_KEY_COUNTRY_FLAG = 'country-flag';

    public const SUBGROUP_KEY_CURRENCY = 'currency';

    public const SUBGROUP_KEY_DISHWARE = 'dishware';

    public const SUBGROUP_KEY_DRINK = 'drink';

    public const SUBGROUP_KEY_EMOTION = 'emotion';

    public const SUBGROUP_KEY_EVENT = 'event';

    public const SUBGROUP_KEY_FACE_AFFECTION = 'face-affection';

    public const SUBGROUP_KEY_FACE_CONCERNED = 'face-concerned';

    public const SUBGROUP_KEY_FACE_COSTUME = 'face-costume';

    public const SUBGROUP_KEY_FACE_GLASSES = 'face-glasses';

    public const SUBGROUP_KEY_FACE_HAND = 'face-hand';

    public const SUBGROUP_KEY_FACE_HAT = 'face-hat';

    public const SUBGROUP_KEY_FACE_NEGATIVE = 'face-negative';

    public const SUBGROUP_KEY_FACE_NEUTRAL_SKEPTICAL = 'face-neutral-skeptical';

    public const SUBGROUP_KEY_FACE_SLEEPY = 'face-sleepy';

    public const SUBGROUP_KEY_FACE_SMILING = 'face-smiling';

    public const SUBGROUP_KEY_FACE_TONGUE = 'face-tongue';

    public const SUBGROUP_KEY_FACE_UNWELL = 'face-unwell';

    public const SUBGROUP_KEY_FAMILY = 'family';

    public const SUBGROUP_KEY_FLAG = 'flag';

    public const SUBGROUP_KEY_FOOD_ASIAN = 'food-asian';

    public const SUBGROUP_KEY_FOOD_FRUIT = 'food-fruit';

    public const SUBGROUP_KEY_FOOD_MARINE = 'food-marine';

    public const SUBGROUP_KEY_FOOD_PREPARED = 'food-prepared';

    public const SUBGROUP_KEY_FOOD_SWEET = 'food-sweet';

    public const SUBGROUP_KEY_FOOD_VEGETABLE = 'food-vegetable';

    public const SUBGROUP_KEY_GAME = 'game';

    public const SUBGROUP_KEY_GENDER = 'gender';

    public const SUBGROUP_KEY_GEOMETRIC = 'geometric';

    public const SUBGROUP_KEY_HAIR_STYLE = 'hair-style';

    public const SUBGROUP_KEY_HANDS = 'hands';

    public const SUBGROUP_KEY_HAND_FINGERS_CLOSED = 'hand-fingers-closed';

    public const SUBGROUP_KEY_HAND_FINGERS_OPEN = 'hand-fingers-open';

    public const SUBGROUP_KEY_HAND_FINGERS_PARTIAL = 'hand-fingers-partial';

    public const SUBGROUP_KEY_HAND_PROP = 'hand-prop';

    public const SUBGROUP_KEY_HAND_SINGLE_FINGER = 'hand-single-finger';

    public const SUBGROUP_KEY_HOTEL = 'hotel';

    public const SUBGROUP_KEY_HOUSEHOLD = 'household';

    public const SUBGROUP_KEY_KEYCAP = 'keycap';

    public const SUBGROUP_KEY_LIGHT_VIDEO = 'light-video';

    public const SUBGROUP_KEY_LOCK = 'lock';

    public const SUBGROUP_KEY_MAIL = 'mail';

    public const SUBGROUP_KEY_MATH = 'math';

    public const SUBGROUP_KEY_MEDICAL = 'medical';

    public const SUBGROUP_KEY_MONEY = 'money';

    public const SUBGROUP_KEY_MONKEY_FACE = 'monkey-face';

    public const SUBGROUP_KEY_MUSIC = 'music';

    public const SUBGROUP_KEY_MUSICAL_INSTRUMENT = 'musical-instrument';

    public const SUBGROUP_KEY_OFFICE = 'office';

    public const SUBGROUP_KEY_OTHER_OBJECT = 'other-object';

    public const SUBGROUP_KEY_OTHER_SYMBOL = 'other-symbol';

    public const SUBGROUP_KEY_PERSON = 'person';

    public const SUBGROUP_KEY_PERSON_ACTIVITY = 'person-activity';

    public const SUBGROUP_KEY_PERSON_FANTASY = 'person-fantasy';

    public const SUBGROUP_KEY_PERSON_GESTURE = 'person-gesture';

    public const SUBGROUP_KEY_PERSON_RESTING = 'person-resting';

    public const SUBGROUP_KEY_PERSON_ROLE = 'person-role';

    public const SUBGROUP_KEY_PERSON_SPORT = 'person-sport';

    public const SUBGROUP_KEY_PERSON_SYMBOL = 'person-symbol';

    public const SUBGROUP_KEY_PHONE = 'phone';

    public const SUBGROUP_KEY_PLACE_BUILDING = 'place-building';

    public const SUBGROUP_KEY_PLACE_GEOGRAPHIC = 'place-geographic';

    public const SUBGROUP_KEY_PLACE_MAP = 'place-map';

    public const SUBGROUP_KEY_PLACE_OTHER = 'place-other';

    public const SUBGROUP_KEY_PLACE_RELIGIOUS = 'place-religious';

    public const SUBGROUP_KEY_PLANT_FLOWER = 'plant-flower';

    public const SUBGROUP_KEY_PLANT_OTHER = 'plant-other';

    public const SUBGROUP_KEY_PUNCTUATION = 'punctuation';

    public const SUBGROUP_KEY_RELIGION = 'religion';

    public const SUBGROUP_KEY_SCIENCE = 'science';

    public const SUBGROUP_KEY_SKIN_TONE = 'skin-tone';

    public const SUBGROUP_KEY_SKY_WEATHER = 'sky-weather';

    public const SUBGROUP_KEY_SOUND = 'sound';

    public const SUBGROUP_KEY_SPORT = 'sport';

    public const SUBGROUP_KEY_SUBDIVISION_FLAG = 'subdivision-flag';

    public const SUBGROUP_KEY_TIME = 'time';

    public const SUBGROUP_KEY_TOOL = 'tool';

    public const SUBGROUP_KEY_TRANSPORT_AIR = 'transport-air';

    public const SUBGROUP_KEY_TRANSPORT_GROUND = 'transport-ground';

    public const SUBGROUP_KEY_TRANSPORT_SIGN = 'transport-sign';

    public const SUBGROUP_KEY_TRANSPORT_WATER = 'transport-water';

    public const SUBGROUP_KEY_WARNING = 'warning';

    public const SUBGROUP_KEY_WRITING = 'writing';

    public const SUBGROUP_KEY_ZODIAC = 'zodiac';
}
