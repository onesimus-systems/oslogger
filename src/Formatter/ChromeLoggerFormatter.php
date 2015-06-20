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

use \ReflectionClass;
use \ReflectionProperty;
use \ReflectionException;

class ChromeLoggerFormatter extends AbstractFormatter
{
    // Line formatter for non-objects
    private $formatter;

    // Keeps track of processed objects
    protected $_processed = array();

    public function __construct()
    {
        $this->formatter = new LineFormatter('{message}');
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
    public function format($level, $message, array $context = array())
    {
        if (!is_object($message)) {
            return $this->formatter->format('', $message, $context);
        }

        //Mark this object as processed so we don't convert it twice and it
        //Also avoid recursion when objects refer to each other
        $this->_processed[] = $message;

        $object_as_array = array();

        // first add the class name
        $object_as_array['___class_name'] = get_class($message);

        // loop through object vars
        $object_vars = get_object_vars($message);

        foreach ($object_vars as $key => $value) {
            // same instance as parent object
            if ($value === $message || in_array($value, $this->_processed, true)) {
                $value = 'recursion - parent object [' . get_class($value) . ']';
            }
            $object_as_array[$key] = $this->format('', $value, $context);
        }

        $reflection = new ReflectionClass($message);

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
                $value = $property->getValue($message);
            } catch (ReflectionException $e) {
                $value = 'only PHP 5.3 can access private/protected properties';
            }

            // same instance as parent object
            if ($value === $message || in_array($value, $this->_processed, true)) {
                $value = 'recursion - parent object [' . get_class($value) . ']';
            }

            // Next line changed from original project
            $object_as_array[$type] = $this->formatObjectHandleArray($value, $context);
        }

        return array($object_as_array);
    }

    /**
     * Prosesses array values
     *
     * @param  mixed $value
     * @return mixed
     */
    protected function formatObjectHandleArray($value, array $context)
    {
        if (is_array($value)) {
            $objs = array();
            foreach ($value as $key => $value2) {
                $objs[$key] = $this->format('', $value2, $context);
            }
            return $objs;
        } else {
            return $this->format('', $value, $context);
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
}
