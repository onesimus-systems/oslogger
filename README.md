OSLogger
--------

OSLogger is a PSR3 compatible, modularized logger. It allows for multiple logging targets through the use of adaptors.

Requirements
------------

- PHP >= 5.3.0

Usage
-----

OSLogger works by using adaptors to write to multiple targets. These adaptors can be for the usual things like a file, or database. They can also be created for more complex things such as sockets and streams. You can make your own adaptors or use one of the ones provided.

Here's how the FileAdaptor would be used:

```php
$adaptor = new \Onesimus\Logger\Adaptors\FileAdaptor('logfile.log');
$logger = new \Onesimus\Logger\Logger($adaptor);

$logger->error("Here's a message with {placeholders}", array('placeholders' => 'turtles'));
// The log message will be "Here's a message with turtles" due to the placeholder interpolation
// per PSR3 spec.
```

If you don't want to actually log anything but don't want to add conditional logging to your application, you can use a NullAdaptor or simple not provide an adaptor to the Logger constructor (a NullAdaptor is made by the class at construction if one isn't provided).

Multiple adaptors can be added to the same logger by calling `Logger::addAdaptor($adaptor)`:

```php
$adaptor2 = new \Onesimus\Logger\Adaptors\EchoAdaptor();
$logger->addAdaptor($adaptor2);
```

When a log is generated it will be sent to all registered logging destinations. So you can have multiple FileAdaptors logging to separate places, a FileAdaptor and database adaptor, what ever you want.

Special Handlers
----------------

Logger comes with builtin handlers for PHP errors, shutdowns, and uncaught exceptions. If you wish to use any of these, call the methods `registerErrorHandler()`, `registerShutdownHandler()`, or `registerExceptionHandler()`. You may use all or non and of course you can use your own handlers by call the appropiate PHP functions. The handlers will take the errors or exceptions and log them using an appropiate log level.

Handler log levels:

- Shutdown: All are `critical`
- Exception: All are `critical`
- Errors:
    - E_USER_ERROR, E_RECOVERABLE_ERROR are `error`
    - E_USER_WARNING, E_WARNING are `warning`
    - E_USER_NOTICE, E_NOTICE are `notice`
    - E_STRICT are `debug`

Adaptors (\Onesimus\Logger\Adaptors)
--------

NullAdaptor
-----------

Logging blackhole. All logs are thrown away and never seen again. Saves to /dev/null

EchoAdaptor
-----------

Echo all messages. That's all.

- `setEchoString($string)` - Sets the template used to echo log messages. Use the placeholders {message}, {level}, and {levelU} (uppercase level) to place the appropiate pieces.
- `getEchoString()` - Returns the currently assigned echo template.

ConsoleAdaptor
--------------

Fancier version of EchoAdaptor that echos logs with color and better default formatting

- `setTextColor($levels, $color)` - Set the color used for the level tag in logs. Color codes can be accessed through the Logger\AsciiCodes class. $levels can be either a string for a single log level, or an array of levels.

FileAdaptor
-----------

Saves logs to files.

- `__construct($filename)`
- `fileLogLevels($levels, $filename)` - Save specific log levels to separate files. Eg: `fileLogLevels(['emergancy', 'alert'], 'the_world_is_ending.log');`
- `disableLogLevels($levels)` - Disable saving specific log levels. Eg: `disableLogLevels(['notice', 'info', 'debug']);`
- `setDefaultFile($filename)` - Set the default file if a specific file hasn't been defined by fileLogLevels(). The constructor calls this with the filename it's given.

License
-------

OSLogger is released under the terms of the BSD 3-Clause license. The full license text is available in the LICENSE.md file.

Versioning
----------

For transparency into the release cycle and in striving to maintain backwards compatibility, This library is maintained under the Semantic Versioning guidelines. Sometimes we screw up, but we'll adhere to these rules whenever possible.

Releases will be numbered with the following format:

`<major>.<minor>.<patch>`

And constructed with the following guidelines:

- Breaking backward compatibility **bumps the major** while resetting minor and patch
- New additions without breaking backward compatibility **bumps the minor** while resetting the patch
- Bug fixes and misc changes **bumps only the patch**

For more information on SemVer, please visit <http://semver.org/>.
