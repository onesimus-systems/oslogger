<?php
namespace Onesimus\Logger;

class MockLogger extends Logger
{
    public function getAdaptors()
    {
        return $this->adaptors;
    }
}

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOf()
    {
        $logger = new Logger();
        $this->assertInstanceOf('Psr\Log\LoggerInterface', $logger);
    }

    public function testNullAdaptorWithEmptyConstructor()
    {
        $logger = new MockLogger();
        $adaptors = $logger->getAdaptors();

        $this->assertEquals(1, count($adaptors));
        $this->assertInstanceOf('Onesimus\Logger\Adaptors\NullAdaptor', $adaptors[0]);
    }

    public function testAddAdditionalAdaptor()
    {
        $logger = new MockLogger();
        $logger->addAdaptor(new Adaptors\EchoAdaptor());
        $adaptors = $logger->getAdaptors();

        $this->assertEquals(2, count($adaptors));
        $this->assertInstanceOf('Onesimus\Logger\Adaptors\EchoAdaptor', $adaptors[1]);
    }

    public function testEmergancyLevel()
    {
        $this->expectOutputString('emergency: Message contents');
        $echo = new Adaptors\EchoAdaptor('{level}: {message}');
        $logger = new Logger($echo);
        $logger->emergency('Message contents');
    }

    public function testAlertLevel()
    {
        $this->expectOutputString('alert: Message contents');
        $echo = new Adaptors\EchoAdaptor('{level}: {message}');
        $logger = new Logger($echo);
        $logger->alert('Message contents');
    }

    public function testCriticalLevel()
    {
        $this->expectOutputString('critical: Message contents');
        $echo = new Adaptors\EchoAdaptor('{level}: {message}');
        $logger = new Logger($echo);
        $logger->critical('Message contents');
    }

    public function testErrorLevel()
    {
        $this->expectOutputString('error: Message contents');
        $echo = new Adaptors\EchoAdaptor('{level}: {message}');
        $logger = new Logger($echo);
        $logger->error('Message contents');
    }

    public function testWarningLevel()
    {
        $this->expectOutputString('warning: Message contents');
        $echo = new Adaptors\EchoAdaptor('{level}: {message}');
        $logger = new Logger($echo);
        $logger->warning('Message contents');
    }

    public function testNoticeLevel()
    {
        $this->expectOutputString('notice: Message contents');
        $echo = new Adaptors\EchoAdaptor('{level}: {message}');
        $logger = new Logger($echo);
        $logger->notice('Message contents');
    }

    public function testInfoLevel()
    {
        $this->expectOutputString('info: Message contents');
        $echo = new Adaptors\EchoAdaptor('{level}: {message}');
        $logger = new Logger($echo);
        $logger->info('Message contents');
    }

    public function testDebugLevel()
    {
        $this->expectOutputString('debug: Message contents');
        $echo = new Adaptors\EchoAdaptor('{level}: {message}');
        $logger = new Logger($echo);
        $logger->debug('Message contents');
    }

    public function testGenericLogFunction()
    {
        $this->expectOutputString('custom: Message contents');
        $echo = new Adaptors\EchoAdaptor('{level}: {message}');
        $logger = new Logger($echo);
        $logger->log('custom', 'Message contents');
    }

    public function testMessagePlaceholderInterplation()
    {
        $this->expectOutputString('error: Message contents on line 25');
        $echo = new Adaptors\EchoAdaptor('{level}: {message}');
        $logger = new Logger($echo);
        $logger->log('error', 'Message contents on line {line}', array('line' => '25'));
    }

    public function testUncaughtExceptionHandler()
    {
        $this->expectOutputRegex("/critical: [\\w\\s]+\\| File: [\\w\\s\\/\\.]+\\| Ln: \\d+ \\| ST: #\\d (.|\\n)+/");
        $echo = new Adaptors\EchoAdaptor('{level}: {message}');
        $logger = new Logger($echo);
        $exception = new \LogicException('Method not defined', 2);
        $logger->PHPExceptionHandler($exception);
    }
}
