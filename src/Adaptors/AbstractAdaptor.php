<?php
/**
 * OSLogger is a lightweight, versatile logging system for PHP applications
 *
 * @author Lee Keitel <keitellf@gmail.com>
 * @copyright 2015 Lee Keitel, Onesimus Systems
 *
 * @license BSD 3-Clause
 *
 */
namespace Onesimus\Logger\Adaptors;

use \Onesimus\Logger\Logger;
use \Onesimus\Logger\Formatter\AbstractFormatter;

use \Psr\Log\LogLevel;

abstract class AbstractAdaptor implements AdaptorInterface
{
    // Minimum log level to handle
    private $minimumLevel = 7;

    // Maximum log level to handle
    private $maximumLevel = 0;

    // Last log message
    private $lastLogLine = '';

    // Formatter used by adaptor
    protected $formatter;

    // Adaptor name
    protected $adaptorName = '';

    /**
     * Checks if the log level is handled with this adaptor
     *
     * @param  string  $level Log level
     * @return boolean
     */
    public function isHandling($level)
    {
        $min = $this->minimumLevel;
        $max = $this->maximumLevel;
        $lev = Logger::$levels[$level];

        return ($min >= $lev && $lev >= $max);
    }

    /**
     * Sets the minimum log level handled by this adaptor
     *
     * @param string $level Log level
     */
    public function setLevel($min = null, $max = null)
    {
        // Null for default allows easier calling to set
        // a maximum level only
        if (!$min || !Logger::isLogLevel($min)) {
            $min = LogLevel::DEBUG;
        }

        if (!$max || !Logger::isLogLevel($max)) {
            $max = LogLevel::EMERGENCY;
        }

        $this->minimumLevel = Logger::$levels[$min];
        $this->maximumLevel = Logger::$levels[$max];
    }

    /**
     * Set the date format
     *
     * @param string $format
     */
    public function setDateFormat($format)
    {
        if ($this->formatter) {
            $this->formatter->setDateFormat($format);
        }
    }

    /**
     * Restores default date format
     */
    public function restoreDateFormat()
    {
        if ($this->formatter) {
            $this->setDateFormat('Y-m-d H:i:s T');
        }
    }

    /**
     * Get current date format
     *
     * @return string
     */
    public function getDateFormat()
    {
        if ($this->formatter) {
            return $this->formatter->getDateFormat();
        }
    }

    /**
     * Return last log message
     *
     * @return string
     */
    public function getLastLogLine()
    {
        return $this->lastLogLine;
    }

    /**
     * Set last log line
     *
     * @param string $line Last message
     */
    protected function setLastLogLine($line)
    {
        $this->lastLogLine = $line;
    }

    /**
     * Set adaptor name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->adaptorName = $name;
    }

    /**
     * Get adaptor name
     *
     * @return string
     */
    public function getName()
    {
        return $this->adaptorName;
    }

    protected function setFormatter(AbstractFormatter $formatter)
    {
        $this->formatter = $formatter;
    }

    protected function format($level, $message, $context = array())
    {
        return $this->formatter->format($level, $message, $context);
    }

    /**
     * Write a log
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @return mixed
     */
    abstract public function write($level, $message, array $context = array());
}
