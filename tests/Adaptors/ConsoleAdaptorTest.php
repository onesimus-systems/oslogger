<?php
namespace Onesimus\Logger;

use \Psr\Log\LogLevel;

class ConsoleAdaptorTest extends \PHPUnit_Framework_TestCase
{
    private $dateRegex = "\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2} \\w{3}";

    public function testExtendsAndImplements()
    {
        $consoleAdaptor = new Adaptors\ConsoleAdaptor();
        $this->assertInstanceOf('\Onesimus\Logger\Adaptors\AbstractAdaptor', $consoleAdaptor);
        $this->assertInstanceOf('\Onesimus\Logger\Adaptors\AdaptorInterface', $consoleAdaptor);
    }

    public function testBasicLog()
    {
        $consoleAdaptor = new Adaptors\ConsoleAdaptor();

        ob_start();
        $consoleAdaptor->write(LogLevel::INFO, 'Hello');
        $output = ob_get_clean();

        $match = preg_match("/{$this->dateRegex}: \033\[36mINFO\033\[22;39m: Hello\n/", $output);
        $this->assertEquals(1, $match);
    }

    public function testSetTextColor()
    {
        $consoleAdaptor = new Adaptors\ConsoleAdaptor();
        $consoleAdaptor->setTextColor('info', AsciiCodes::FG_COLOR_BLUE);

        ob_start();
        $consoleAdaptor->write(LogLevel::INFO, 'Hello');
        $output = ob_get_clean();

        $match = preg_match("/{$this->dateRegex}: \033\[34mINFO\033\[22;39m: Hello\n/", $output);
        $this->assertEquals(1, $match);
    }
}
