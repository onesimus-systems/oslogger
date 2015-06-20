<?php
namespace Onesimus\Logger;

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    /*
     * Most tests will be done with the EchoAdaptor since it's the easiest
     * to test with. All adaptors have their own tests that are ran as well.
     */

    public function testImplementsPsr3()
    {
        $logger = new Logger();
        $this->assertInstanceOf('Psr\Log\LoggerInterface', $logger);
    }

    public function testNullAdaptorWithEmptyConstructor()
    {
        $logger = new Logger();
        $adaptors = $logger->getAdaptors();

        $this->assertEquals(1, count($adaptors));
        $this->assertInstanceOf('Onesimus\Logger\Adaptors\NullAdaptor', $adaptors[0]);
    }

    public function testAddAdditionalAdaptor()
    {
        $logger = new Logger();
        $logger->addAdaptor(new Adaptors\EchoAdaptor());
        $adaptors = $logger->getAdaptors();

        $this->assertEquals(2, count($adaptors));
        $this->assertInstanceOf('Onesimus\Logger\Adaptors\EchoAdaptor', $adaptors[1]);
    }

    public function testEmergancyLevel()
    {
        $this->expectOutputString('emergency: Message contents');
        $echo = new Adaptors\EchoAdaptor('debug', '{level}: {message}');
        $logger = new Logger($echo);
        $logger->emergency('Message contents');
    }

    public function testAlertLevel()
    {
        $this->expectOutputString('alert: Message contents');
        $echo = new Adaptors\EchoAdaptor('debug', '{level}: {message}');
        $logger = new Logger($echo);
        $logger->alert('Message contents');
    }

    public function testCriticalLevel()
    {
        $this->expectOutputString('critical: Message contents');
        $echo = new Adaptors\EchoAdaptor('debug', '{level}: {message}');
        $logger = new Logger($echo);
        $logger->critical('Message contents');
    }

    public function testErrorLevel()
    {
        $this->expectOutputString('error: Message contents');
        $echo = new Adaptors\EchoAdaptor('debug', '{level}: {message}');
        $logger = new Logger($echo);
        $logger->error('Message contents');
    }

    public function testWarningLevel()
    {
        $this->expectOutputString('warning: Message contents');
        $echo = new Adaptors\EchoAdaptor('debug', '{level}: {message}');
        $logger = new Logger($echo);
        $logger->warning('Message contents');
    }

    public function testNoticeLevel()
    {
        $this->expectOutputString('notice: Message contents');
        $echo = new Adaptors\EchoAdaptor('debug', '{level}: {message}');
        $logger = new Logger($echo);
        $logger->notice('Message contents');
    }

    public function testInfoLevel()
    {
        $this->expectOutputString('info: Message contents');
        $echo = new Adaptors\EchoAdaptor('debug', '{level}: {message}');
        $logger = new Logger($echo);
        $logger->info('Message contents');
    }

    public function testDebugLevel()
    {
        $this->expectOutputString('debug: Message contents');
        $echo = new Adaptors\EchoAdaptor('debug', '{level}: {message}');
        $logger = new Logger($echo);
        $logger->debug('Message contents');
    }

    /**
     * @expectedException Psr\Log\InvalidArgumentException
     */
    public function testUndefinedLogLevel()
    {
        $logger = new Logger();
        $logger->log('custom', 'Message contents');
    }

    public function testMessagePlaceholderInterplation()
    {
        $this->expectOutputString('error: Message contents on line 25');
        $echo = new Adaptors\EchoAdaptor('debug', '{level}: {message}');
        $logger = new Logger($echo);
        $logger->log('error', 'Message contents on line {line}', array('line' => '25'));
    }

    public function testUncaughtExceptionHandler()
    {
        $this->expectOutputRegex("/^critical: Method not defined \| File: [\w\s\/\.\-]+\| Ln: \d{1,4} \| ST: (.|\\n|\\r)+$/");
        $echo = new Adaptors\EchoAdaptor('debug', '{level}: {message}');
        $logger = new Logger($echo);
        $handlers = new ErrorHandler($logger);
        $exception = new \LogicException('Method not defined', 2);
        $handlers->PHPExceptionHandler($exception);
    }
}
