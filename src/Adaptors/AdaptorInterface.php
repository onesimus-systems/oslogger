<?php
/**
 * OSLogger is a lightweight, versatile logging system for PHP applications
 *
 * @author Lee Keitel <keitellf@gmail.com>
 * @copyright 2015 Lee Keitel, Onesimus Systems
 *
 * @license BSD 3-Clause
 */
namespace Onesimus\Logger\Adaptors;

interface AdaptorInterface
{
    // Method called by logger to save logs
    public function write($level, $message, array $context = array());
}
