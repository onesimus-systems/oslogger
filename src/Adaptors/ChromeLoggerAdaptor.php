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

use \ReflectionClass;
use \ReflectionProperty;
use \ReflectionException;

use \Psr\Log\LogLevel;

use \Onesimus\Logger\Logger;

class ChromeLoggerAdaptor extends AbstractAdaptor
{
    protected $headerName = 'X-ChromeLogger-Data';

    // Keeps track of processed objects
    protected $_processed = array();

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
        LogLevel::NOTICE    => 'warn',
        LogLevel::INFO      => 'info',
        LogLevel::DEBUG     => 'info'
    );

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
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
            $backtraceLine = "{$backtrace[0]['file']} : {$backtrace[0]['line']}";
        }

        if (in_array($backtraceLine, $this->backtraces)) {
            $backtraceLine = '';
        } else {
            $this->backtraces []= $backtraceLine;
        }

        $this->json['rows'] []= array(
            $this->formatObject($message),
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
     * Format objects into JSON objects
     *
     * Borrowed from:
     * https://github.com/ccampbell/chromephp/blob/c3c297615d48ae5b2a86a82311152d1ed095fcef/ChromePhp.php#L283
     *
     * @param  mixed $object
     * @return mixed
     */
    protected function formatObject($object)
    {
        if (!is_object($object)) {
            return $object;
        }

        //Mark this object as processed so we don't convert it twice and it
        //Also avoid recursion when objects refer to each other
        $this->_processed[] = $object;

        $object_as_array = array();

        // first add the class name
        $object_as_array['___class_name'] = get_class($object);

        // loop through object vars
        $object_vars = get_object_vars($object);

        foreach ($object_vars as $key => $value) {
            // same instance as parent object
            if ($value === $object || in_array($value, $this->_processed, true)) {
                $value = 'recursion - parent object [' . get_class($value) . ']';
            }
            $object_as_array[$key] = $this->formatObject($value);
        }

        $reflection = new ReflectionClass($object);

        // loop through the properties and add those
        foreach ($reflection->getProperties() as $property) {
            // if one of these properties was already added above then ignore it
            if (array_key_exists($property->getName(), $object_vars)) {
                continue;
            }

            $type = $this->getPropertyKey($property);
            if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
                $property->setAccessible(true);
            }

            try {
                $value = $property->getValue($object);
            } catch (ReflectionException $e) {
                $value = 'only PHP 5.3 can access private/protected properties';
            }

            // same instance as parent object
            if ($value === $object || in_array($value, $this->_processed, true)) {
                $value = 'recursion - parent object [' . get_class($value) . ']';
            }

            // Next line changed from original project
            $object_as_array[$type] = $this->formatObjectHandleArray($value);
        }

        return array($object_as_array);
    }

    /**
     * Prosesses array values
     *
     * @param  mixed $value
     * @return mixed
     */
    protected function formatObjectHandleArray($value)
    {
        if (is_array($value)) {
            $objs = array();
            foreach ($value as $key => $value2) {
                $objs[$key] = $this->formatObject($value2);
            }
            return $objs;
        } else {
            return $this->formatObject($value);
        }
    }

    /**
     * Return description string of object property
     *
     * Borrowed from:
     * https://github.com/ccampbell/chromephp/blob/c3c297615d48ae5b2a86a82311152d1ed095fcef/ChromePhp.php#L347
     *
     * @param  ReflectionProperty $property
     * @return string
     */
    protected function getPropertyKey(ReflectionProperty $property)
    {
        $static = $property->isStatic() ? ' static' : '';
        if ($property->isPublic()) {
            return 'public' . $static . ' ' . $property->getName();
        }
        if ($property->isProtected()) {
            return 'protected' . $static . ' ' . $property->getName();
        }
        if ($property->isPrivate()) {
            return 'private' . $static . ' ' . $property->getName();
        }
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
