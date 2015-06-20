<?php
/**
 * OSLogger is a lightweight, versatile logging system for PHP applications
 *
 * @author Lee Keitel <keitellf@gmail.com>
 * @copyright 2015 Lee Keitel, Onesimus Systems
 *
 * @license BSD 3-Clause
 *
 * ConsoleAdaptor echos logs to stdout with color and pleasant format
 */
namespace Onesimus\Logger\Adaptors;

use \Onesimus\Logger\AsciiCodes;

use \Psr\Log\LogLevel;

class ConsoleAdaptor extends AbstractAdaptor
{
    protected $templateString;

    protected $defaultColor = AsciiCodes::FG_COLOR_WHITE;

    protected $levelColors = array(
        LogLevel::EMERGENCY => AsciiCodes::FG_COLOR_RED,
        LogLevel::ALERT     => AsciiCodes::FG_COLOR_RED,
        LogLevel::CRITICAL  => AsciiCodes::FG_COLOR_RED,
        LogLevel::ERROR     => AsciiCodes::FG_COLOR_RED,
        LogLevel::WARNING   => AsciiCodes::FG_COLOR_ORANGE,
        LogLevel::NOTICE    => AsciiCodes::FG_COLOR_ORANGE,
        LogLevel::INFO      => AsciiCodes::FG_COLOR_CYAN,
        LogLevel::DEBUG     => AsciiCodes::FG_COLOR_CYAN
    );

    public function __construct($level = LogLevel::DEBUG)
    {
        $this->setLevel($level);
        $this->templateString = "{date}: {color}{levelU}".AsciiCodes::RESET_FG_COLOR.": {message}\n";
    }

    /**
     * Set the text color for specified log level(s)
     *
     * @param string/array $level Log levels to which this color applies
     * @param string $color Text color to set
     */
    public function setTextColor($level, $color)
    {
        if (!is_array($level)) {
            $level = array($level);
        }

        foreach ($level as $loglevel) {
            $this->levelColors[$loglevel] = $color;
        }
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
        $color = $this->defaultColor;
        if (isset($this->levelColors[$level]) && $this->levelColors[$level]) {
            $color = $this->levelColors[$level];
        }

        $replacements = array(
            '{level}' => $level,
            '{levelU}' => strtoupper($level),
            '{message}' => $message,
            '{date}' => date($this->dateFormat),
            '{color}' => $color
        );
        $log = strtr($this->templateString, $replacements);
        $this->setLastLogLine($log);
        echo $log;
    }
}
