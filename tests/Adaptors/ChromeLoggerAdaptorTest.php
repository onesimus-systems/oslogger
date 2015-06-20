<?php
namespace Onesimus\Logger;

use \Psr\Log\LogLevel;

class TestChromeAdaptor extends Adaptors\ChromeLoggerAdaptor
{
    protected $headers = array();

    protected function sendHeader($content)
    {
        $this->headers[$this->headerName] = $content;
    }

    public function getHeaders()
    {
        return $this->headers;
    }
}

class ChromeLoggerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'OSLogger Test; Chrome/1.0';
    }

    public function testHeaders()
    {
        $handler = new TestChromeAdaptor();
        $handler->logBacktrace(false);
        $handler->write(LogLevel::DEBUG, 'test');
        $handler->write(LogLevel::WARNING, 'something bad');

        $expected = array(
            'X-ChromeLogger-Data' => base64_encode(utf8_encode(json_encode(array(
                'version' => Logger::VERSION,
                'columns' => array('log', 'backtrace', 'type'),
                'rows' => array(
                    array(
                        'test',
                        '',
                        'info'
                    ),
                    array(
                        'something bad',
                        '',
                        'warn'
                    )
                )
            ))))
        );

        $this->assertEquals($expected, $handler->getHeaders());
    }

    public function testHeaderOverflow()
    {
        $handler = new TestChromeAdaptor();
        $handler->logBacktrace(false);
        $handler->write(LogLevel::DEBUG, 'test');
        $handler->write(LogLevel::WARNING, str_repeat('a', 150*1024));

        // Overflow!!
        $handler->write(LogLevel::WARNING, str_repeat('a', 200*1024));

        $expected = array(
            'X-ChromeLogger-Data' => base64_encode(utf8_encode(json_encode(array(
                'version' => Logger::VERSION,
                'columns' => array('log', 'backtrace', 'type'),
                'rows' => array(
                    array(
                        'test',
                        '',
                        'info'
                    ),
                    array(
                        str_repeat('a', 150*1024),
                        '',
                        'warn'
                    ),
                    array(
                        'Logs truncated, exceeded Chrome header size limit',
                        '',
                        ''
                    )
                )
            ))))
        );

        $this->assertEquals($expected, $handler->getHeaders());
    }
}
