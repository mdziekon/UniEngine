# UniEngine

[![Build Status](https://travis-ci.org/mdziekon/UniEngine.svg?branch=master)](https://travis-ci.org/mdziekon/UniEngine)

OGame-clone browser based game engine.

---

- [Requirements](#requirements)
- [Installation](#installation)
- [Development guides](#development-guides)
- [Credits](#credits)
- [License](#license)

## Requirements

- PHP
    - ``>= 5.4``
    - ``< 7.0``
- MySQL
    - ``>= 5``
- A webserver (eg. nginx)

### Development requirements

- Composer
    - ``>= 1.6``
- Node.js
    - ``>= 11``

## Installation

1. Setup a webserver capable of running PHP scripts.
    - ``php.ini`` file should have ``E_NOTICE`` reporting disabled, eg.:
        - ``error_reporting = E_ALL & ~E_NOTICE & ~E_STRICT``
    - PHP needs to have write permissions to these files / directories:
        - ``config.php``
            - (one-off, installation purposes)
        - ``includes/constants.php``
            - (one-off, installation purposes)
        - ``tmp/``
            - (permanent, eg. for Smarty cache)
2. Setup a MySQL server.
3. Create a DB user and DB database for your game server.
4. Move source files of the project to your webserver's directory.
5. Run installation wizard: http://your_server_address:port/install
6. Remove ``install/`` directory

## Development guides

### Preparations

1. Install PHP dependencies:
    - ``composer install``
2. Install Node.js dependencies:
    - ``npm ci``

### Available scripts

- Run PHP code linting (powered by PHP Code Sniffer)
    - ``composer run-script ci-php-phpcs``
- Run JavaScript code linting (powered by ESLint):
    - ``npm run ci-js-eslint``
- Run CSS code linting (powered by stylelint):
    - ``npm run ci-css-stylelint``
- Rebuild (minification + cachebusting) JS & CSS files:
    - ``npm run build-minify``
    - All files from ``js/`` and ``css/`` directories will be re-minified (only when actually changed) and saved in their respective ``dist/`` directories.
    - _Note:_ when a file has no changes, this script **won't** remove the old minified & cachebusted file from ``dist/``. File replacement happens only if a source file has changes, or there is no result file yet.
    - _Note:_ this script does **not** automatically replace filepaths in templates. For now, this has to be done manually by a developer.
    - _Note:_ due to legacy reasons, all files in ``dist/`` are stored in the repo.

---

## Credits

### Authors

- Michał Dziekoński (https://github.com/mdziekon)

### Contributors

- Alessio <nicoales@live.it> (https://github.com/XxidroxX)

## License

GPL-2.0

See ``LICENSE`` file for this project's license details.

See ``OTHERLICENSES`` for the licenses of included external resources.
