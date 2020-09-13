const PhpFile = require('./php-file')

class PhpInterface extends PhpFile {
  constructor (...props) {
    super('interface', ...props)
  }
}

module.exports = PhpInterface
