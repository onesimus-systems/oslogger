<?php
namespace Onesimus\Logger;

class NullAdaptorTest extends \PHPUnit_Framework_TestCase
{
    public function testLiterallyNothing()
    {
        $this->expectOutputString('');
        $nullAdaptor = new Adaptors\NullAdaptor();
        $logger = new Logger($nullAdaptor);
        $logger->info('Hello');
    }
}
