<?php
namespace Onesimus\Logger;

use \Psr\Log\LogLevel;

use \Onesimus\Logger\Adaptors\FileAdaptor;

class FileAdaptorTest extends \PHPUnit_Framework_TestCase
{
    public function testExtendsAndImplements()
    {
        $fileAdaptor = new FileAdaptor('');
        $this->assertInstanceOf('\Onesimus\Logger\Adaptors\AbstractAdaptor', $fileAdaptor);
        $this->assertInstanceOf('\Onesimus\Logger\Adaptors\AdaptorInterface', $fileAdaptor);
    }

    public function testInitialDefaultFile()
    {
        $fa = new FileAdaptor('/logs/log.txt');
        $this->assertEquals('/logs/log.txt', $fa->getDefaultFile());
    }

    public function testSetDefaultFile()
    {
        $fa = new FileAdaptor('');
        $fa->setDefaultFile('/var/logs/log.txt');
        $this->assertEquals('/var/logs/log.txt', $fa->getDefaultFile());
    }

    public function testSeparateLevelFilenames()
    {
        $fa = new FileAdaptor('/logs/log.txt');
        $levelFiles = $fa->getLogLevelFiles();
        $this->assertEquals('', $levelFiles[LogLevel::EMERGENCY]);

        $fa->setLogLevelFile(LogLevel::EMERGENCY, 'emergency_log.txt');
        $levelFiles = $fa->getLogLevelFiles();
        $this->assertEquals('/logs/emergency_log.txt', $levelFiles[LogLevel::EMERGENCY]);
    }

    public function testAutomaticSeparateLevelFilenames()
    {
        $fa = new FileAdaptor('log.txt');
        $fa->separateLogFiles();

        $expectedFilenames = array(
            LogLevel::EMERGENCY => './emergency.txt',
            LogLevel::ALERT     => './alert.txt',
            LogLevel::CRITICAL  => './critical.txt',
            LogLevel::ERROR     => './error.txt',
            LogLevel::WARNING   => './warning.txt',
            LogLevel::NOTICE    => './notice.txt',
            LogLevel::INFO      => './info.txt',
            LogLevel::DEBUG     => './debug.txt'
        );

        $this->assertEquals($expectedFilenames, $fa->getLogLevelFiles());
    }

    public function testWriteLog()
    {
        $fa = new FileAdaptor(__DIR__.'/logs/log.txt');
        $fa->write(LogLevel::ERROR, 'Hello');

        $lastLine = $fa->getLastLogLine();
        $logFile = file(__DIR__.'/logs/log.txt', FILE_SKIP_EMPTY_LINES);

        $this->assertEquals($lastLine, $logFile[count($logFile)-1]);

        unlink(__DIR__.'/logs/log.txt');
    }

    public function testLogLevelFile()
    {
        $fa = new FileAdaptor(__DIR__.'/logs/log.txt');
        $fa->setLogLevelFile(LogLevel::EMERGENCY, 'emergency_log.txt');

        $fa->write(LogLevel::EMERGENCY, 'Hello');

        $lastLine = $fa->getLastLogLine();
        $logFile = file(__DIR__.'/logs/emergency_log.txt', FILE_SKIP_EMPTY_LINES);

        $this->assertEquals($lastLine, $logFile[count($logFile)-1]);

        unlink(__DIR__.'/logs/emergency_log.txt');
    }
}
