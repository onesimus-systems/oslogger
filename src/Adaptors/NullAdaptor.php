<?php
/**
 * OSLogger is a lightweight, versatile logging system for PHP applications
 *
 * @author Lee Keitel <keitellf@gmail.com>
 * @copyright 2015 Lee Keitel, Onesimus Systems
 *
 * @license BSD 3-Clause
 *
 * NullAdaptor can be given the to Logger if logging is not desired for some reason but allows
 * all current logging code to continue working as usual.
 */
namespace Onesimus\Logger\Adaptors;

class NullAdaptor implements AdaptorInterface
{
    /**
     * Write logs to the void
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function write($level, $message, array $context = array())
    {
        // NOOP
    }
}
