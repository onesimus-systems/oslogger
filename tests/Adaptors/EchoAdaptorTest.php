<?php
namespace Onesimus\Logger;

class EchoAdaptorTest extends \PHPUnit_Framework_TestCase
{
    public function testEchoEchoEcho()
    {
        $this->expectOutputString("Log Level: info\nMessage: Hello\n\n");
        $echoAdaptor = new Adaptors\EchoAdaptor();
        $logger = new Logger($echoAdaptor);
        $logger->info('Hello');
    }

    public function testCustomEchoPattern()
    {
        $dateRegex = "\\D{3},\\s\\d{2}\\s\\D{3}\\s\\d{4}\\s[\\d:]{8}\\s\\+\\d{4}";
        $this->expectOutputRegex("/$dateRegex WARNING: Dragons are on the loose\n/");
        $echoAdaptor = new Adaptors\EchoAdaptor("{date} {levelU}: {message}\n");
        $logger = new Logger($echoAdaptor);
        $logger->warning('Dragons are on the loose');
    }

    public function testCustomEchoPattern2()
    {
        $this->expectOutputString("WARNING: Dragons are on the loose\n");
        $echoAdaptor = new Adaptors\EchoAdaptor();
        $echoAdaptor->setEchoString("{levelU}: {message}\n");
        $logger = new Logger($echoAdaptor);
        $logger->warning('Dragons are on the loose');
    }
}
