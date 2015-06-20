<?php
/**
 * OSLogger is a lightweight, versatile logging system for PHP applications
 *
 * @author Lee Keitel <keitellf@gmail.com>
 * @copyright 2015 Lee Keitel, Onesimus Systems
 *
 * @license BSD 3-Clause
 *
 * ChromeLoggerAdaptor interfaces with the Chrome Logger extension.
 * The spec is available at https://craig.is/writing/chrome-logger/techspecs
 *
 * Indicated methods are taken from the chromephp project located here:
 * https://github.com/ccampbell/chromephp. Chromephp is licensed under
 * Apache version 2.
 */
namespace Onesimus\Logger\Adaptors;

use \Psr\Log\LogLevel;

use \Onesimus\Logger\Logger;
use \Onesimus\Logger\Formatter\ChromeLoggerFormatter;

class ChromeLoggerAdaptor extends AbstractAdaptor
{
    protected $headerName = 'X-ChromeLogger-Data';

    // Keep track of backtraces
    protected $backtraces = array();

    // Send the header or no
    protected $sendHeader = true;

    // Triggers user agent check on first send
    protected $initialized = false;

    // Add a backtrace to logs
    protected $logBacktrace = true;

    // Have we overflowed
    protected $overflowed = false;

    // Chrome Logger schema
    protected $json = array(
        'version' => Logger::VERSION,
        'columns' => array('log', 'backtrace', 'type'),
        'rows' => array()
    );

    // Maps PSR3 log levels to Chrome Logger levels
    protected $logLevelMappings = array(
        LogLevel::EMERGENCY => 'error',
        LogLevel::ALERT     => 'error',
        LogLevel::CRITICAL  => 'error',
        LogLevel::ERROR     => 'error',
        LogLevel::WARNING   => 'warn',
        LogLevel::NOTICE    => '',
        LogLevel::INFO      => 'info',
        LogLevel::DEBUG     => ''
    );

    public function __construct()
    {
        $this->setFormatter(new ChromeLoggerFormatter());
    }

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
        if ($this->overflowed){
            return;
        }

        $backtraceLine = '';
        if ($this->logBacktrace) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3);
            $backtrace = array_pop($backtrace);
            $backtraceLine = "{$backtrace['file']} : {$backtrace['line']}";
        }

        if (in_array($backtraceLine, $this->backtraces)) {
            $backtraceLine = '';
        } else {
            $this->backtraces []= $backtraceLine;
        }

        $this->json['rows'] []= array(
            $this->format('', $message, array('__context'=>$context)),
            $backtraceLine,
            $this->logLevelMappings[$level]
        );

        $this->setLastLogLine($message);
        $this->send();
    }

    public function logBacktrace($onoff)
    {
        $this->logBacktrace = $onoff;
    }

    /**
     * Prepare to send header
     */
    protected function send()
    {
        if (!$this->initialized) {
            $this->initialized = true;
            $this->sendHeader = $this->headersAccepted();

            if (!$this->sendHeader) {
                return;
            }
        }

        $jsonEncoded = json_encode($this->json);
        $jsonEncoded = str_replace("\n", '', $jsonEncoded);
        $encoded = base64_encode(utf8_encode($jsonEncoded));

        // Note it's 240KB to leave room for an error message
        if (strlen($encoded) > 240*1024) {
            $this->overflowed = true;

            $this->json['rows'][count($this->json['rows']) - 1] = array(
                'Logs truncated, exceeded Chrome header size limit',
                '',
                ''
            );

            $jsonEncoded = json_encode($this->json);
            $jsonEncoded = str_replace("\n", '', $jsonEncoded);
            $encoded = base64_encode(utf8_encode($jsonEncoded));
        }

        $this->sendHeader($encoded);
    }

    /**
     * Send header to Chrome
     *
     * @param  string $data Encoded data string
     */
    protected function sendHeader($data)
    {
        if (!headers_sent()) {
            header("{$this->headerName}: {$data}");
        }
    }

    /**
     * Checkes user agent to make sure it's Chrome
     *
     * @return boolean
     */
    protected function headersAccepted()
    {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            return false;
        }
        return (bool) preg_match('{\bChrome/\d+[\.\d+]*\b}', $_SERVER['HTTP_USER_AGENT']);
    }
}
