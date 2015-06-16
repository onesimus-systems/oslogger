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

class EchoAdaptor implements AdaptorInterface
{
    protected $echoString = "Log Level: {level}\nMessage: {message}\n\n";

    public function __construct($echoStr = '')
    {
        if ($echoStr) {
            $this->echoString = $echoStr;
        }
    }

    /**
     * Set the echo string
     *
     * @param string $echoStr Echo pattern with placeholders:
     *                        {level} replaced with the log level
     *                        {levelU} replaced with uppercase log level
     *                        {message} replaced with the log message
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
            '{date}' => date(DATE_RFC2822)
        );
        echo strtr($this->echoString, $replacements);
    }
}
