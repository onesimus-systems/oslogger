<?php
/**
 * OSLogger is a lightweight, versatile logging system for PHP applications
 *
 * @author Lee Keitel <keitellf@gmail.com>
 * @copyright 2015 Lee Keitel, Onesimus Systems
 *
 * @license BSD 3-Clause
 */
namespace Onesimus\Logger;

use \Psr\Log\LogLevel;
use \Psr\Log\LoggerInterface;
use \Psr\Log\InvalidArgumentException;

use \Onesimus\Logger\Adaptors\AbstractAdaptor;

class Logger implements LoggerInterface
{
    const VERSION = "2.3.2";

    // Array of adaptors to save logs
    protected $adaptors = array();

    // Counts number of adaptors during lifetime
    // This should never decrement
    protected $adaptorCount = 0;

    // Name of logger
    protected $name = '';

    // Array of log levels with int values
    public static $levels = array(
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT     => 1,
        LogLevel::CRITICAL  => 2,
        LogLevel::ERROR     => 3,
        LogLevel::WARNING   => 4,
        LogLevel::NOTICE    => 5,
        LogLevel::INFO      => 6,
        LogLevel::DEBUG     => 7
    );

    /**
     * Constructor function
     *
     * @param Adaptors\AdaptorInterface $adaptor Adaptor to use for logging
     *                            If one isn't given, a NullAdaptor is used
     */
    public function __construct(AbstractAdaptor $adaptor = null, $loggerName = '')
    {
        if (!$adaptor) {
            $adaptor = new Adaptors\NullAdaptor();
        }
        $this->addAdaptor($adaptor);
        $this->setName($loggerName);
    }

    /**
     * Add an adaptor to write logs
     *
     * @param AbstractAdaptor $adaptor Adaptor to add to list
     */
    public function addAdaptor(AbstractAdaptor $adaptor)
    {
        // The key will be the adaptor's name or the next numerical
        // count if a name is blank
        $adaptorName = $adaptor->getName() ?: $this->adaptorCount;
        $this->adaptors[$adaptorName] = $adaptor;
        $this->adaptorCount++;
    }

    /**
     * Retrieve array of current adaptors
     *
     * @return array
     */
    public function getAdaptors()
    {
        return $this->adaptors;
    }

    /**
     * Determines if the logger has an adaptor named $name
     *
     * @param  string  $name Name of adaptor to look for
     * @return boolean
     */
    public function hasAdaptor($name)
    {
        return array_key_exists($name, $this->adaptors);
    }

    /**
     * Removes an adaptor from the logger
     *
     * @param  string $name Name of adaptor to remove
     */
    public function removeAdaptor($name)
    {
        if ($this->hasAdaptor($name)) {
            unset($this->adaptors[$name]);
        }
    }

    /**
     * Set logger name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get logger name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Determines if a string is a valid log level
     *
     * @param  string  $level Log level to check
     * @return boolean
     */
    public static function isLogLevel($level)
    {
        return array_key_exists($level, self::$levels);
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array())
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array())
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array())
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array())
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array())
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array())
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array())
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array())
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        if (!self::isLogLevel($level)) {
            throw new InvalidArgumentException('Unknown security level');
        }

        // Send data to adaptor(s)
        foreach ($this->adaptors as $adaptor) {
            if ($adaptor->isHandling($level)) {
                $adaptor->write($level, $message, $context);
            }
        }
    }
}
