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

class LineFormatter extends AbstractFormatter
{
    protected $pattern = 'No log pattern set';

    public function __construct($pattern)
    {
        $this->setPattern($pattern);
    }

    public function setPattern($pattern)
    {
        $this->pattern = $pattern;
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function format($level, $message, array $context = array())
    {
        $message = $this->normalize($message, $context);

        $replacements = array(
            '{level}' => $level,
            '{levelU}' => strtoupper($level),
            '{message}' => $message,
            '{date}' => date($this->dateFormat)
        );

        foreach ($context as $holder => $value) {
            if ($holder === '__context') {
                continue;
            }
            $replacements['{'.$holder.'}'] = $value;
        }

        return strtr($this->pattern, $replacements);
    }

    protected function normalize($message, array $context)
    {
        if (is_scalar($message)) {
            $message = (string) $message;

            if ($context['__context']) {
                $message = $this->interpolateMessage($message, $context['__context']);
            }
            return $message;
        }

        if (is_null($message)) {
            return 'NULL';
        }

        if (is_array($message) || $message instanceof \Traversable) {
            $normalized = array();
            $normalizedCount = 0;

            foreach ($message as $key => $value) {
                if (++$normalizedCount > 1000) {
                    $normalized[$key] = 'Traversable exceeds 1000 items, aborting normalization';
                    break;
                }
                $normalized[$key] = $this->normalize($value, $context);
            }

            return $this->removeNewLines($this->toJson($normalized, true));
        }

        if (is_object($message)) {
            if ($message instanceof \Exception) {
                return $this->normalizeException($message);
            }

            // non-serializable objects that implement __toString stringified
            if (method_exists($message, '__toString') && !$message instanceof \JsonSerializable) {
                $value = (string) $message;
            } else {
                // the rest is json-serialized in some way
                //$value = $this->toJson($message, true);
                $value = print_r($this->obj2array($message), true);
            }

            return sprintf("[object] (%s: %s)", get_class($message), $value);
        }

        if (is_resource($message)) {
            return '[resource]';
        }

        return '[Unknown type]';
    }

    protected function interpolateMessage($message, array $context)
    {
        $message = (string) $message;
        $replace = array();

        // Interpolate context values
        foreach ($context as $key => $data) {
            $replace['{'.$key.'}'] = $data;
        }
        return strtr($message, $replace);
    }

    /**
     * From the Monolog library
     * @license MIT
     * @param  Exception $e
     * @return [type]       [description]
     */
    protected function normalizeException(\Exception $e)
    {
        $data = array(
            'class' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile().':'.$e->getLine(),
        );

        $trace = $e->getTrace();
        foreach ($trace as $frame) {
            if (isset($frame['file'])) {
                $data['trace'][] = $frame['file'].':'.$frame['line'];
            } else {
                // We should again normalize the frames, because it might contain invalid items
                $data['trace'][] = $this->toJson($this->normalize($frame, array()), true);
            }
        }

        if ($previous = $e->getPrevious()) {
            $data['previous'] = $this->normalizeException($previous);
        }

        return $this->toJson($data);
    }

    /**
     * From the Monolog library
     * @license MIT
     * @param  mixed  $data
     * @param  boolean $ignoreErrors
     * @return string
     */
    protected function toJson($data, $ignoreErrors = false)
    {
        // suppress json_encode errors since it's twitchy with some inputs
        if ($ignoreErrors) {
            if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
                return @json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
            return @json_encode($data);
        }

        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return json_encode($data);
    }

    protected function removeNewLines($line)
    {
        $line = preg_replace('{[\n\r|\n|\r]}', '', $line);
        return $line;
    }

    protected function obj2array(&$Instance)
    {
        $clone = (array) $Instance;
        $rtn = array();

        foreach ($clone as $key => $value) {
            $aux = explode("\0", $key);
            $var = $aux[count($aux)-1];

            if (count($aux) === 1) {
                $newkey = 'public:'.$var;
            } elseif ($aux[1] === '*') {
                $newkey = 'protected:'.$var;
            } else {
                $newkey = 'private:'.$var;
            }

            $rtn[$newkey] = &$clone[$key];
        }
        return $rtn;
    }
}
