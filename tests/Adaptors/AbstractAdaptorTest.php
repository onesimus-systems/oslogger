<?php
namespace Onesimus\Logger;

use \Psr\Log\LogLevel;

class NewAdaptor extends Adaptors\AbstractAdaptor
{
    public function write($l, $m, array $c = array())
    {
        // NOOP
    }

    public function setLastLogLine($line)
    {
        parent::setLastLogLine($line);
    }
}

class AbstractAdaptorTest extends \PHPUnit_Framework_TestCase
{
    public function testImplements()
    {
        $adaptor = new NewAdaptor();
        $this->assertInstanceOf('\Onesimus\Logger\Adaptors\AdaptorInterface', $adaptor);
    }

    public function testGetSetAdaptorName()
    {
        $adaptor = new NewAdaptor();
        $this->assertEquals('', $adaptor->getName());

        $adaptor->setName('newadaptor');
        $this->assertEquals('newadaptor', $adaptor->getName());
    }

    public function testDefaultLogLevel()
    {
        $adaptor = new NewAdaptor();
        $this->assertTrue($adaptor->isHandling(LogLevel::DEBUG));
    }

    public function testSetLevel()
    {
        $adaptor = new NewAdaptor();
        $adaptor->setLevel(LogLevel::WARNING);
        $this->assertTrue($adaptor->isHandling(LogLevel::WARNING));
        $this->assertFalse($adaptor->isHandling(LogLevel::NOTICE));
    }

    public function testSetBadLevel()
    {
        $adaptor = new NewAdaptor();
        $adaptor->setLevel('');
        $this->assertFalse($adaptor->isHandling(LogLevel::EMERGENCY));
        $this->assertFalse($adaptor->isHandling(LogLevel::ALERT));
        $this->assertFalse($adaptor->isHandling(LogLevel::CRITICAL));
        $this->assertFalse($adaptor->isHandling(LogLevel::ERROR));
        $this->assertFalse($adaptor->isHandling(LogLevel::WARNING));
        $this->assertFalse($adaptor->isHandling(LogLevel::NOTICE));
        $this->assertFalse($adaptor->isHandling(LogLevel::INFO));
        $this->assertFalse($adaptor->isHandling(LogLevel::DEBUG));
    }

    public function testGetSetDateFormat()
    {
        $adaptor = new NewAdaptor();
        $this->assertEquals('Y-m-d H:i:s T', $adaptor->getDateFormat());

        $adaptor->setDateFormat(DATE_RFC2822);
        $this->assertEquals(DATE_RFC2822, $adaptor->getDateFormat());
    }

    public function testGetSetLastLogLine()
    {
        $adaptor = new NewAdaptor();
        $this->assertEquals('', $adaptor->getLastLogLine());

        $adaptor->setLastLogLine('Hello, my name is Boris');
        $this->assertEquals('Hello, my name is Boris', $adaptor->getLastLogLine());
    }
}
