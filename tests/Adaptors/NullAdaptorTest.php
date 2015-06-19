<?php
namespace Onesimus\Logger;

use \Psr\Log\LogLevel;

class NullAdaptorTest extends \PHPUnit_Framework_TestCase
{
    public function testExtendsAndImplements()
    {
        $nullAdaptor = new Adaptors\NullAdaptor();
        $this->assertInstanceOf('\Onesimus\Logger\Adaptors\AbstractAdaptor', $nullAdaptor);
        $this->assertInstanceOf('\Onesimus\Logger\Adaptors\AdaptorInterface', $nullAdaptor);
    }

    public function testLiterallyNothing()
    {
        $this->expectOutputString('');
        $nullAdaptor = new Adaptors\NullAdaptor();
        $nullAdaptor->write(LogLevel::INFO, 'Nothing');
    }
}
