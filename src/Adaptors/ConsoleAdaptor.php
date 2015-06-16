<?php
/**
 * OSLogger is a lightweight, versatile logging system for PHP applications
 *
 * @author Lee Keitel <keitellf@gmail.com>
 * @copyright 2015 Lee Keitel, Onesimus Systems
 *
 * @license BSD 3-Clause
 *
 * ConsoleAdaptor echos logs to stdout with color and pleasent format
 */
namespace Onesimus\Logger\Adaptors;

use \Onesimus\Logger\AsciiCodes;

class ConsoleAdaptor implements AdaptorInterface
{
    protected $templateString = '{date}: {color}{levelU}'.AsciiCodes::RESET_FG_COLOR.": {message}\n";
    protected $defaultColor = AsciiCodes::FG_COLOR_WHITE;
    protected $levelColors = array(
        'emergency' => AsciiCodes::FG_COLOR_RED,
        'alert' => AsciiCodes::FG_COLOR_RED,
        'critical' => AsciiCodes::FG_COLOR_RED,
        'error' => AsciiCodes::FG_COLOR_RED,
        'warning' => AsciiCodes::FG_COLOR_ORANGE,
        'notice' => AsciiCodes::FG_COLOR_ORANGE,
        'info' => AsciiCodes::FG_COLOR_CYAN,
        'debug' => AsciiCodes::FG_COLOR_CYAN
    );

    public function __construct() {}

    /**
     * Set the text color for specified log level(s)
     *
     * @param string/array $level Log levels to which this color applies
     * @param string $color Text color to set
     */
    public function setTextColor($level, $color)
    {
        if (!is_array($level)) {
            $level = [$level];
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
            '{date}' => date('Y-d-m H:i:s T'),
            '{color}' => $color
        );
        echo strtr($this->templateString, $replacements);
    }
}
