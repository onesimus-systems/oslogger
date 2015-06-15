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
        echo "Log Level: $level\nMessage: $message\n\n";
    }
}
