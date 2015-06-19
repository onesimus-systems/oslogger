<?php
/**
 * OSLogger is a lightweight, versatile logging system for PHP applications
 *
 * @author Lee Keitel <keitellf@gmail.com>
 * @copyright 2015 Lee Keitel, Onesimus Systems
 *
 * @license BSD 3-Clause
 *
 * FileAdaptor saves logs to a file or multiple files per log level.
 */
namespace Onesimus\Logger\Adaptors;

use \Onesimus\Logger\Logger;

use \Psr\Log\LogLevel;

class FileAdaptor implements AdaptorInterface
{
    // File to use if a specific loglevel isn't defined
    protected $fallbackFile = '';

    // Minimum log level to save
    protected $logLevelThreshold = LogLevel::DEBUG;

    // Files to use for each log level
    protected $filenameLevels = array(
        LogLevel::EMERGENCY => '',
        LogLevel::ALERT     => '',
        LogLevel::CRITICAL  => '',
        LogLevel::ERROR     => '',
        LogLevel::WARNING   => '',
        LogLevel::NOTICE    => '',
        LogLevel::INFO      => '',
        LogLevel::DEBUG     => ''
    );

    public function __construct($file = '')
    {
        $this->setDefaultFile($file);
    }

    /**
     * Assign a file for specific log levels
     *
     * @param string/array $level Log level(s) that use the given $filename
     * @param string $filename File to write logs
     */
    public function fileLogLevels($level, $filename)
    {
        if (!is_array($level)) {
            $level = [$level];
        }

        foreach ($level as $loglevel) {
            $this->filenameLevels[$loglevel] = $filename;
        }
    }

    /**
     * Set minimum threshold a log must meet to be saved
     *
     * @param string $level Log level to set as threshold
     */
    public function setLevelThreshold($level = LogLevel::DEBUG)
    {
        $this->logLevelThreshold = $level;
    }

    /**
     * Disable log levels from being saved
     *
     * @param string/array $level Log level(s) to disable
     */
    public function disableLogLevels($level)
    {
        $this->fileLogLevels($level, false);
    }

    /**
     * Set the primary/fallback file for log levels without a specific filename
     *
     * @param string $file - Filename for default save file
     */
    public function setDefaultFile($file)
    {
        $this->fallbackFile = $file;
    }

    /**
     * Write the logs to a file
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function write($level, $message, array $context = array())
    {
        // Return for disabled levels
        if ($this->filenameLevels[$level] === false) {
            return true;
        }

        // Return for logs below threshold
        if (Logger::$levels[$this->logLevelThreshold] < Logger::$levels[$level]) {
            return true;
        }

        $filename = $this->filenameLevels[$level] ?: $this->fallbackFile;
        $message = date("Y-m-d H:i:s") . ' | ' . $level . ' | Message: ' . $message.PHP_EOL;

        return file_put_contents($filename, $message, FILE_APPEND | LOCK_EX);
    }
}
