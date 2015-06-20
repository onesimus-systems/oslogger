<?php
/**
 * OSLogger is a lightweight, versatile logging system for PHP applications
 *
 * @author Lee Keitel <keitellf@gmail.com>
 * @copyright 2015 Lee Keitel, Onesimus Systems
 *
 * @license BSD 3-Clause
 */
namespace Onesimus\Logger\Formatter;

abstract class AbstractFormatter
{
    protected $dateFormat = 'Y-m-d H:i:s T';

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
     * Get current date format
     *
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    abstract public function format($level, $message, array $context = array());
}
