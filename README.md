# Pasteboard

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This is a very simple static utility class that gives access to the `pbcopy` and `pbpaste` interface on MacOS and other systems that support those shell commands. This utility is primarily useful in developing PHP command line tools. It endeavors to comply fully with PRS-1, PRS-2, and PRS-4.

This project also uses the [thephpleague/skeleton](https://github.com/thephpleague/skeleton) repository for component boilerplate.

## Install

Via Composer

``` bash
$ composer require Dlindberg/Pasteboard
```

## Usage

Pasteboard has three methods:

### Get

Retrieves a value from the clipboard.

```php
Pasteboard::get();
```

***Parameters:*** None.

***Returns:*** The contents of the host computers clipboard or `false` if the clipboard is empty or fails to return a value.

***Note:*** no filters or santization is applied to the return value and it is prudent to treat the value as unsafe user input for the purposes of your application.

### Set

Copies a value to the clipboard.

```php
Pasteboard::set(string $value);
```

***Parameters*** `$value` any value that the pasteboard on the host computer accepts. The function does not validate that the value is safe or valid before attempting to pass it to the host computer. If you are sending user input, or the results of an http request to the computer's clipboard it is prudent ensure that whatever is sent to the clipboard is safe for the context you intend to paste it to.

***Returns:*** `true` on success or `false` on failure. 

### Set Array

Sends an array of values one at a time to the clipboard pausing between each send. The function also has some advanced options defined in the $options array.

```php
Pasteboard::setArray(array $values, array $options)
```

***Parameters***

* `$values` an array of values you wish to send to the host computer. The same notes as in set apply to each value.
* `$options` an optional settings array. Available options:
    * `reset` when `true` the original contents of the clipboard will be restored at the end of execution. **Default:** `false`.
    * `wait` amount of time in seconds to wait between `set()` operations. **Default** `1`.
    * `depth` Specified recursion depth. **Default:** `0`, nested arrays will be skipped.
    * `heartbeat` a user defined closure function that will execute each time the clipboard is set. This function overrides the default `$hearbeat` function. Therefore, the `wait` parameter is ignored when a `hearbeat` is passed. The function should return a truthy value if execution should continue and a falsy value to terminate execution. Heartbeat is passed the result of `set()`.

***Returns:*** `true` on success or `false` on failure.

***Note:*** Without an advanced clipboard manager on the host machine, or passing a heartbeat function sending more than two to three items to clipboard will rapidly become untenable.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email dane@lindberg.xyz instead of using the issue tracker.

## Credits

- [Dane Lindberg][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/dlindberg/Pasteboard.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/dlindberg/Pasteboard/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/dlindberg/Pasteboard.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/dlindberg/Pasteboard.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/dlindberg/Pasteboard.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/dlindberg/Pasteboard
[link-travis]: https://travis-ci.org/dlindberg/Pasteboard
[link-scrutinizer]: https://scrutinizer-ci.com/g/dlindberg/Pasteboard/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/dlindberg/Pasteboard
[link-downloads]: https://packagist.org/packages/dlindberg/Pasteboard
[link-author]: https://github.com/dlindberg
[link-contributors]: ../../contributors
