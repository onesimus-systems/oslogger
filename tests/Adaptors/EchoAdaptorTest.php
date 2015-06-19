<?php
namespace Onesimus\Logger;

use \Psr\Log\LogLevel;

class EchoAdaptorTest extends \PHPUnit_Framework_TestCase
{
    private $dateRegex = "\\d{4}-\\d{2}-\\d{2} \\d{2}:\\d{2}:\\d{2} \\w{3}";

    public function testExtendsAndImplements()
    {
        $echoAdaptor = new Adaptors\EchoAdaptor();
        $this->assertInstanceOf('\Onesimus\Logger\Adaptors\AbstractAdaptor', $echoAdaptor);
        $this->assertInstanceOf('\Onesimus\Logger\Adaptors\AdaptorInterface', $echoAdaptor);
    }

    public function testEchoEchoEcho()
    {
        $this->expectOutputRegex("/{$this->dateRegex} \[info\] Message: Hello\n/");
        $echoAdaptor = new Adaptors\EchoAdaptor();
        $echoAdaptor->write(LogLevel::INFO, 'Hello');
    }

    public function testCustomEchoPattern()
    {
        $this->expectOutputRegex("/{$this->dateRegex} WARNING: Dragons are on the loose\n/");
        $echoAdaptor = new Adaptors\EchoAdaptor('debug', "{date} {levelU}: {message}\n");
        $echoAdaptor->write(LogLevel::WARNING, 'Dragons are on the loose');
    }

    public function testCustomEchoPattern2()
    {
        $this->expectOutputString("WARNING: Dragons are on the loose\n");
        $echoAdaptor = new Adaptors\EchoAdaptor();
        $echoAdaptor->setEchoString("{levelU}: {message}\n");
        $echoAdaptor->write(LogLevel::WARNING, 'Dragons are on the loose');
    }
}
