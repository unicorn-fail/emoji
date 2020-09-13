const emojibase = require('emojibase')
const metaGroups = require('emojibase-data/meta/groups.json')

const emojibaseGroups = {}

emojibaseGroups.GROUPS = [];
Object.values(metaGroups.groups).forEach((g) => {
    const key = g.toUpperCase().replace(/-/g, '_')
    emojibaseGroups.GROUPS.push(`self::GROUP_KEY_${key}`);
    emojibaseGroups[`GROUP_KEY_${key}`] = emojibase[`GROUP_KEY_${key}`]
})
emojibaseGroups.SUBGROUPS = [];
Object.values(metaGroups.subgroups).forEach((g) => {
    const key = g.toUpperCase().replace(/-/g, '_')
    emojibaseGroups.SUBGROUPS.push(`self::SUBGROUP_KEY_${key}`);
    emojibaseGroups[`SUBGROUP_KEY_${key}`] = g
})

module.exports = emojibaseGroups
