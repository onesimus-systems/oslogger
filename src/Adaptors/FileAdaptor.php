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

use RuntimeException;
use \Psr\Log\LogLevel;

use \Onesimus\Logger\Formatter\LineFormatter;

class FileAdaptor extends AbstractAdaptor
{
    // File to use if a specific loglevel isn't defined
    protected $defaultFile = '';

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

    /**
     * Constructor
     *
     * @param string $file  Default logfile
     * @param string $level Minimum log level this adaptor handles
     */
    public function __construct($file, $level = LogLevel::DEBUG)
    {
        $this->setDefaultFile($file);
        $this->setLevel($level);

        $formatter = new LineFormatter("{date}: [{level}] Message: {message}\n");
        $this->setFormatter($formatter);
    }

    /**
     * Assign a file for specific log levels
     *
     * @param string/array $level Log level(s) that use the given $filename
     * @param string $filename File to write logs
     */
    public function setLogLevelFile($level, $filename)
    {
        $dir = dirname($this->defaultFile);

        if (!is_array($level)) {
            $level = array($level);
        }

        foreach ($level as $loglevel) {
            $this->filenameLevels[$loglevel] = $dir.DIRECTORY_SEPARATOR.$filename;
        }
    }

    public function getLogLevelFiles()
    {
        return $this->filenameLevels;
    }

    /**
     * Convenience function to make each log level go to a separate file.
     * It uses the directory name of the current default file.
     */
    public function separateLogFiles($ext = '.txt')
    {
        foreach ($this->filenameLevels as $level => $filename) {
            $this->setLogLevelFile($level, $level.$ext);
        }
    }

    /**
     * Set the primary/fallback file for log levels without a specific filename
     *
     * @param string $file - Filename for default save file
     */
    public function setDefaultFile($file)
    {
        $this->defaultFile = $file;
    }

    public function getDefaultFile()
    {
        return $this->defaultFile;
    }

    /**
     * Write the logs to a file
     *
     * @param string $level
     * @param string $message
     * @param array $context
     * @return bool
     */
    public function write($level, $message, array $context = array())
    {
        $filename = $this->filenameLevels[$level] ?: $this->defaultFile;
        $context = array('__context' => $context);
        $log = $this->format($level, $message, $context);
        $logDir = dirname($filename);

        // Error are suppressed because an Exception will be thrown instead
        if (!is_dir($logDir)) {
            if (@mkdir($logDir, 0777, true) === false) {
                throw new RuntimeException('Failed to create log directory. Please check permissions.');
            }
        }

        if (@file_put_contents($filename, $log, FILE_APPEND | LOCK_EX) === false) {
            throw new RuntimeException('Failed to write log file. Please check permissions.');
        }

        $this->setLastLogLine($log);

        return true;
    }
}
