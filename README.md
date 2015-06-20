OSLogger
--------

[![Build Status](https://travis-ci.org/onesimus-systems/oslogger.svg)](https://travis-ci.org/onesimus-systems/oslogger)

OSLogger is a PSR3 compatible, modularized logger. It allows for multiple logging targets through the use of adaptors.

Requirements
------------

- PHP >= 5.3.0

Features
--------

- Quick and easy setup
- [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) compliant
- Multiple adaptors:
    - Files
    - Console
    - Echo
    - Null
    - [Chrome Logger](https://craig.is/writing/chrome-logger)
- Set minimum and maximum handled log level for an adaptor

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

If you don't want to actually log anything but don't want to add conditional logging to your application, you can use a NullAdaptor or simple not provide an adaptor to the Logger constructor (a NullAdaptor is made by the object at construction if one isn't provided).

Multiple adaptors can be added to the same logger by calling `Logger::addAdaptor($adaptor)`:

```php
$adaptor2 = new \Onesimus\Logger\Adaptors\EchoAdaptor();
$logger->addAdaptor($adaptor2);
```

When a log is generated it will be sent to all registered adapters that are set to handle the particular log level. So you can have multiple FileAdaptors logging to separate places, a FileAdaptor and database adaptor, what ever you want. You can also have logs above or below a certain threshold log to one place and all logs to another. You can completely customize how the logger works for you.

Special Handlers
----------------

OSLogger comes with builtin handlers for PHP errors, shutdowns (only does something if error_get_last() returns anything), and uncaught exceptions. If you wish to use any of these, create a new `Logger\ErrorHandler` object and call the methods `registerErrorHandler()`, `registerShutdownHandler($loglevel)`, or `registerExceptionHandler($loglevel)` and pass in a Logger object. The handlers will take the errors or exceptions and log them using an appropiate log level.

Handler log levels:

- Shutdown: All are `critical` (unless specified otherwise)
- Exception: All are `critical` (unless specified otherwise)
- Errors:
    - E_USER_ERROR, E_RECOVERABLE_ERROR are `error`
    - E_USER_WARNING, E_WARNING are `warning`
    - E_USER_NOTICE, E_NOTICE are `notice`
    - E_STRICT are `debug`

Note: The shutdown handler will only do something if the function error_get_last() returns anything. The handler does not call exit() or die() so you can register another shutdown handler.

Adaptors (\Onesimus\Logger\Adaptors)
------------------------------------

###All Adaptors

- `isHandling($level)` - Check if the adaptor handles logs at the given level.
- `setLevel($min, $max)` - Set the min/max level handled by the adaptor. If you want to set only the max, pass `null` as the first argument.
- `setDateFormat($format)` - Set the date format used in logs.
- `getDateFormat()` - Get the date format used in logs.
- `restoreDateFormat()` - Sets date format to the default "Y-m-d H:i:s T".
- `getLastLogLine()` - Returns last log line written.
- `setName($name)` - Name of adaptor used by Logger, set before adding to a Logger object.
- `getName()` - Returns name of adaptor

###NullAdaptor

Logging blackhole. All logs are thrown away and never seen again. Saves to /dev/null

###EchoAdaptor

Echo all messages. That's all.

- `__construct($minimumLevel = LogLevel::DEBUG, $echoStr = "")` - $echoStr will default to "{date}: [{level}] Message: {message}\n"

- `setEchoString($string)` - Sets the template used to echo log messages. See the Placeholders section.
- `getEchoString()` - Returns the currently assigned echo template.

###ConsoleAdaptor

Fancier version of EchoAdaptor that echos logs with color and better default formatting

- `__construct($minimumLevel = LogLevel::DEBUG)`

- `setTextColor($levels, $color)` - Set the color used for the level tag in logs. Color codes can be accessed through the Logger\AsciiCodes class. $levels can be either a string for a single log level, or an array of levels.

###FileAdaptor

Saves logs to files.

- `__construct($file, $minimumLevel = LogLevel::DEBUG)`

- `setLogLevelFile($levels, $filename)` - Save specific log levels to separate files. Eg: `fileLogLevels(['emergancy', 'alert'], 'the_world_is_ending.log');`
- `getLogLevelFiles()` - Returns array of current filenames for a level. The array is keyed to the different log levels. An empty value means it uses the default file.
- `separateLogFiles($ext = '.txt')` - Separates all log levels to their own files. $ext is the file extension used for the log files.
- `setDefaultFile($filename)` - Set the default file if a specific file hasn't been defined by fileLogLevels(). The constructor calls this with the filename it's given.
- `getDefaultFile()` - Returns current default file.

###ChromeLoggerAdaptor

Sends logs to Chrome using the Chrome Logger extension. Website: [Chrome Logger](https://craig.is/writing/chrome-logger)

- `logBacktrace($onoff)` - Record a backtrace (file, line #) in logs. Default: true

Placeholders
------------

Some adaptors allow a customized string pattern used when making logs. When this is available, a few placeholders can be used. Placeholders are case-sensative.

- `{level}` - Log level in all lowercase
- `{levelU}` - Log level in all uppercase
- `{message}` - Log message or formatted string for objects
- `{date}` - Datetime of log

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
