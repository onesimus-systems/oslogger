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

class ErrorHandler
{
    protected $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
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
                $this->logger->error($message, $context);
                break;
            case E_WARNING:
                // no break
            case E_USER_WARNING:
                $this->logger->warning($message, $context);
                break;
            case E_NOTICE:
                // no break
            case E_USER_NOTICE:
                $this->logger->notice($message, $context);
                break;
            case E_STRICT:
                $this->logger->debug($message, $context);
                break;
            default:
                $this->logger->warning($message, $context);
        }
        return;
    }

    /**
     * Logger provided shutdown handler
     */
    public function PHPShutdownHandler()
    {
        session_write_close();
        if ($lasterror = error_get_last()) {
            $message = $lasterror['message'] . ' | File: {file} | Ln: {line}';
            $context = array(
                'file' => $lasterror['file'],
                'line' => $lasterror['line']
            );
            $this->logger->critical($message, $context);
        }
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
        $this->logger->critical($message, $context);
    }
}
