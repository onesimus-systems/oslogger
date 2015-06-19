<?php
/**
 * OSLogger is a lightweight, versatile logging system for PHP applications
 *
 * @author Lee Keitel <keitellf@gmail.com>
 * @copyright 2015 Lee Keitel, Onesimus Systems
 *
 * @license BSD 3-Clause
 *
 * EchoAdaptor simple echos the log message.
 */
namespace Onesimus\Logger\Adaptors;

use \Psr\Log\LogLevel;

class EchoAdaptor extends AbstractAdaptor
{
    protected $echoString = "{date} [{level}] Message: {message}\n";

    /**
     * Constructor
     *
     * @param string $level   Minimum handled log level
     * @param string $echoStr Echo pattern
     */
    public function __construct($level = LogLevel::DEBUG, $echoStr = '')
    {
        if ($echoStr) {
            $this->echoString = $echoStr;
        }
        $this->setLevel($level);
    }

    /**
     * Set the echo string
     *
     * @param string $echoStr Echo pattern with placeholders:
     *                        {level} replaced with the log level
     *                        {levelU} replaced with uppercase log level
     *                        {message} replaced with the log message
     *                        {date} replaced with the date in $this->dateFormat format
     */
    public function setEchoString($echoStr)
    {
        $this->echoString = $echoStr;
    }

    /**
     * Retreive current echo string pattern
     *
     * @return string
     */
    public function getEchoString()
    {
        return $this->echoString;
    }

    /**
     * Echo logs to the ether
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function write($level, $message, array $context = array())
    {
        $replacements = array(
            '{level}' => $level,
            '{levelU}' => strtoupper($level),
            '{message}' => $message,
            '{date}' => date($this->dateFormat)
        );
        $log = strtr($this->echoString, $replacements);
        $this->setLastLogLine($log);
        echo $log;
    }
}
