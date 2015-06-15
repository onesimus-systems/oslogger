<?php
/**
 * OSLogger is a lightweight, versatile logging system for PHP applications
 *
 * @author Lee Keitel <keitellf@gmail.com>
 * @copyright 2015 Lee Keitel, Onesimus Systems
 *
 * @license BSD 3-Clause
 */
namespace Onesimus\Logger;

use \Psr\Log\LogLevel;
use \Psr\Log\LoggerInterface;

class Logger implements LoggerInterface
{
    protected $adaptors;

    /**
     * Constructor function
     *
     * @param Adaptors\AdaptorInterface $adaptor Adaptor to use for logging
     *                            If one isn't given, a NullAdaptor is used
     */
    public function __construct(Adaptors\AdaptorInterface $adaptor = null)
    {
        if (!$adaptor) {
            $adaptor = new Adaptors\DummyAdaptor();
        }
        $this->adaptors = [$adaptor];
    }

    /**
     * Add an adaptor to write logs
     *
     * @param Adaptors\AdaptorInterface $adaptor Adaptor to add to list
     */
    public function addAdaptor(Adaptors\AdaptorInterface $adaptor)
    {
        $this->adaptors []= $adaptor;
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array())
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array())
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array())
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array())
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array())
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array())
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array())
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array())
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $message = (string) $message;
        $replace = array();

        // Interpolate context values
        foreach ($context as $key => $data) {
            $replace['{'.$key.'}'] = $data;
        }
        $message = strtr($message, $replace);

        // Send data to adaptor(s)
        foreach ($this->adaptors as $adaptor) {
            $adaptor->write($level, $message, $context);
        }
    }

    /**
     * Register the error handler in Logger for PHP errors
     */
    public function registerErrorHandler()
    {
        set_error_handler(array($this, 'PHPErrorHandler'));
    }

    /**
     * Register the shutdown handler in Logger for critical PHP failures
     */
    public function registerShutdownHandler()
    {
        register_shutdown_function(array($this, 'PHPShutdownHandler'));
    }

    /**
     * Register the exception handler in Logger for unhandled PHP exceptions
     */
    public function registerExceptionHandler()
    {
        set_exception_handler(array($this, 'PHPExceptionHandler'));
    }

    /**
     * Logger provided error handler
     */
    public function PHPErrorHandler($error_level, $error_message, $error_file, $error_line, $error_context)
    {
        $message = $error_message . ' | File: {file} | Ln: {line}';
        $context = array(
            'file' => $error_file,
            'line' => $error_line
        );

        switch ($error_level) {
            case E_USER_ERROR:
                // no break
            case E_RECOVERABLE_ERROR:
                $this->error($message, $context);
                break;
            case E_WARNING:
                // no break
            case E_USER_WARNING:
                $this->warning($message, $context);
                break;
            case E_NOTICE:
                // no break
            case E_USER_NOTICE:
                $this->notice($message, $context);
                break;
            case E_STRICT:
                $this->debug($message, $context);
                break;
            default:
                $this->warning($message, $context);
        }
        return;
    }

    /**
     * Logger provided shutdown handler
     */
    public function PHPShutdownHandler()
    {
        session_write_close();
        $lasterror = error_get_last();
        $message = $lasterror['message'] . ' | File: {file} | Ln: {line}';
        $context = array(
            'file' => $lasterror['file'],
            'line' => $lasterror['line']
        );
        $this->critical($message, $context);
    }

    /**
     * Logger provided uncaught Exception handler
     */
    public function PHPExceptionHandler($exception)
    {
        session_write_close();
        $message = $exception->getMessage() . ' | File: {file} | Ln: {line} | ST: {stacktrace}';
        $context = array(
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'stacktrace' => $exception->getTraceAsString()
        );
        $this->critical($message, $context);
    }
}
