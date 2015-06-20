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

use \Psr\Log\LogLevel;

abstract class AbstractAdaptor implements AdaptorInterface
{
    protected $handleLevel = LogLevel::DEBUG;

    protected $dateFormat = 'Y-m-d H:i:s T';

    private $lastLogLine = '';

    protected $adaptorName = '';

    /**
     * Checks if the log level is handled with this adaptor
     *
     * @param  string  $level Log level
     * @return boolean
     */
    public function isHandling($level)
    {
        if (Logger::isLogLevel($this->handleLevel)) {
            return Logger::$levels[$this->handleLevel] >= Logger::$levels[$level];
        }
        return false;
    }

    /**
     * Sets the minimum log level handled by this adaptor
     *
     * @param string $level Log level
     */
    public function setLevel($level)
    {
        $this->handleLevel = $level;
    }

    /**
     * Set the date format
     *
     * @param string $format
     */
    public function setDateFormat($format)
    {
        $this->dateFormat = $format;
    }

    /**
     * Restores default date format
     */
    public function restoreDateFormat()
    {
        $this->setDateFormat('Y-m-d H:i:s T');
    }

    /**
     * Get current date format
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
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
