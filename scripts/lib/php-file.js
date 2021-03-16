/**
 * @todo Move this to its own public module.
 */

const fs = require('fs')
const path = require('path')

const baseDir = path.resolve(__dirname, '..', '..')
const sourceDir = path.resolve(baseDir, 'src')

const editorConfig = fs.readFileSync(path.resolve(baseDir, '.editorconfig'), `UTF-8`)

// Use ~~ to ensure match is converted into a number isn't NaN.
// @see https://stackoverflow.com/a/21044102
const maxLineLength = ~~(editorConfig.match(/max_line_length = (\d+)/)||[]).pop() || 120
const indentSize = ~~(editorConfig.match(/indent_size = (\d+)/)||[]).pop() || 4
const indentStyle = (editorConfig.match(/indent_style = (space|tab)/)||[]).pop() || 'space'
const indentCharacter = (indentStyle === 'tab' ? `\t` : ' ')

const indent = (indentLevel) => indentCharacter.repeat(indentLevel * indentSize)

const createLine = (indentLevel = 0, line = `\n`, ...values) => {
    line = line.replace(/([\n\r]+)/g, `$1${indent(indentLevel)}`);
    if (values.length) {
        line = line.replace(/%s/g, (string, position, ...args) => {
            const value = values.shift()
            return createValue(indentLevel, value, position)
        })
    }
    if (line.substr(-1) !== `\n`) {
        line += `\n`
    }
    return `${indent(indentLevel)}${line}`
}

const createValue = (indentLevel, value, currentLineLength = 0) => {
    const nextLineIndent = indent(indentLevel + 1)
    if (value === null || value === undefined) {
        return 'null'
    }
    if (value === true || value === false) {
        return value ? 'true' : 'false'
    }
    if (value instanceof RegExp) {
        // Add the unicode modifier if unicode codepoints are found, but it wasn't set.
        if (/\\u[0-9A-F]{4,5}/.test(value.source) && !value.unicode) {
            value = new RegExp(value.source, value.flags + 'u')
        }

        // PHP uses a different unicode codepoint syntax, adjust to match.
        value = value.toString()
            // Unicode codepoint.
            .replace(/\\u{([0-9A-F]{5})}/g, '\\x{$1}')
            // Unicode hex.
            .replace(/\\u([0-9A-F]{4})/g, '\\x{$1}')
    }

    if (typeof value === 'string') {
        // References should not be quoted; return as is.
        if (/self::|static::/.test(value)) {
            return value
        }

        const extraSpace = nextLineIndent.length + 3 // Provide some extra space for incidentals and string wrapping.
        const totalLineLength = currentLineLength + value.length
        if (totalLineLength > maxLineLength) {
            const lineLengthDiff = maxLineLength - (currentLineLength + extraSpace)
            const width = maxLineLength - indent(indentLevel).length - extraSpace
            const regex = new RegExp(`(?![^\n]{1,${width}}$)([^\n]{1,${width}})`, 'g')
            const lines = [
                value.substr(0, lineLengthDiff),
                ...value.substr(lineLengthDiff).replace(regex, `$1\n`).split(`\n`)
            ]
            value = lines
                .map((line) => {
                    if (line.substr(-1) === '\\') {
                        line += '\\'
                    }
                    return line
                })
                .join(`' .\n${nextLineIndent}'`)
        }
        return `'${value}'`
    }
    if (typeof value === 'object') {
        const indexed = Array.isArray(value)
        const keys = Object.keys(value)
        const allNumeric = indexed ? true : keys.reduce((b, n) => b === true && /^\d+$/.test(n), true)
        const propMaxLength = indexed ? false : keys.reduce((n, k) => Math.max(n, k.length), 0)
        let array = '[\n'
        for (let [k, v] of Object.entries(value)) {
            array += nextLineIndent
            v = createValue(indentLevel + 1, v, currentLineLength + nextLineIndent.length)

            // Index array.
            if (indexed) {
                array += `${v},\n`
            }
            // Associative array (all numeric keys or references).
            else if (allNumeric || /^self::|static::/.test(k)) {
                k = `${k}`.padEnd(propMaxLength)
                array += `${k} => ${v},\n`
            }
            // Associative array.
            else {
                k = `'${k}'`.padEnd(propMaxLength + indentSize)
                array += `${k} => ${v},\n`
            }
        }
        array += indent(indentLevel) + ']'
        return array
    }
    return JSON.stringify(value)
}

class PhpFile {

    constructor (type, name, strict = true) {
        this.type = type
        this.namespace = name.split('\\')
        this.name = this.namespace.pop()
        this.constants = {}
        this.strict = strict
    }

    addConstants (constants = {}, exclude = [], scope = 'public') {
        Object.getOwnPropertyNames(constants).sort().forEach((name) => {
            exclude = exclude.map((s) =>  s instanceof RegExp ? s.source : s)

            // Skip certain constants.
            if (exclude.length && new RegExp(exclude.join('|')).test(name)) {
                return
            }

            if (typeof constants[name] !== 'function' && /^[A-Z_]+$/.test(name)) {
                if (this.constants[scope] === undefined) {
                    this.constants[scope] = {}
                }
                this.constants[scope][name] = constants[name]
            }
        })
        return this
    }

    getDir () {
        return path.resolve(sourceDir, ...[...this.namespace].slice(2))
    }

    getFilename () {
        return `${this.name}.php`
    }

    getPath () {
        return path.resolve(this.getDir(), this.getFilename())
    }

    setModule(module) {
        this.module = module
        return this
    }

    toString () {
        let indentLevel = 0
        let output = `<?php\n`

        if (this.strict) {
            output += `\ndeclare(strict_types=1);\n`
        }

        if (this.namespace.length) {
            output += `\nnamespace ${this.namespace.join('\\')};\n`
        }

        if (this.module) {
            output += `
/*!
 * IMPORTANT NOTE!
 *
 * THIS FILE IS BASED ON EXTRACTED DATA FROM THE NPM MODULE:
 *
 * ${indent(1)}https://www.npmjs.com/package/${this.module}
 *
 * DO NOT ATTEMPT TO DIRECTLY MODIFY THIS FILE. ALL MANUAL CHANGES MADE TO THIS FILE
 * WILL BE DESTROYED AUTOMATICALLY THE NEXT TIME IT IS REBUILT.
 */`
        }
        else {
            output += `
/*!
 * IMPORTANT NOTE!
 *
 * THIS FILE IS BASED ON EXTRACTED DATA FROM AN NPM MODULE.
 * DO NOT ATTEMPT TO DIRECTLY MODIFY THIS FILE. ALL MANUAL CHANGES MADE TO THIS FILE
 * WILL BE DESTROYED AUTOMATICALLY THE NEXT TIME IT IS REBUILT.
 */`
        }

        output += `\n${this.type} ${this.name}\n{`

        indentLevel++

        // Render constants.
        for (const [scope, constant] of Object.entries(this.constants).sort()) {
            for (const [name, value] of Object.entries(constant)) {
                output += createLine() + createLine(indentLevel, `${scope} const ${name} = %s;`, value)
            }
        }
        output += `}\n`

        indentLevel--

        return output
    }

    write () {
        const path = this.getPath()
        fs.writeFileSync(path, this.toString())
        console.log(`\nCreated ${path.replace(baseDir, '.')}`)
        return this
    }

}

PhpFile.create = function create (...args) {
    return new this(...args)
}

PhpFile.createLine = createLine

PhpFile.createValue = createValue

PhpFile.indent = indent

PhpFile.indentCharacter = indentCharacter

PhpFile.indentSize = indentSize

PhpFile.indentStyle = indentStyle

module.exports = PhpFile
