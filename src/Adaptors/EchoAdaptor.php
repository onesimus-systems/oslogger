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

use \Onesimus\Logger\Formatter\LineFormatter;

class EchoAdaptor extends AbstractAdaptor
{
    /**
     * Constructor
     *
     * @param string $level   Minimum handled log level
     * @param string $echoStr Echo pattern
     */
    public function __construct($level = LogLevel::DEBUG, $echoStr = '')
    {
        $echoStr = $echoStr ?: "{date}: [{level}] Message: {message}\n";
        $formatter = new LineFormatter($echoStr);
        $this->setFormatter($formatter);
        $this->setLevel($level);
    }

    /**
     * Set the echo string
     *
     * @param string $echoStr Echo pattern
     */
    public function setEchoString($echoStr)
    {
        $this->formatter->setPattern($echoStr);
    }

    /**
     * Retreive current echo string pattern
     *
     * @return string
     */
    public function getEchoString()
    {
        return $this->formatter->getPattern();
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
        $context = array('__context' => $context);

        $log = $this->format($level, $message, $context);
        $this->setLastLogLine($log);
        echo $log;
    }
}
