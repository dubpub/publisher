<?php namespace Dubpub\Publisher\Commands;

use Dubpub\Publisher\PublisherScanner;
use Symfony\Component\Console\Command\Command;

class InitCommandTest extends \HelperTest
{
    /**
     * @param PublisherScanner $publisherScanner
     * @return Command
     */
    public function getCommand(PublisherScanner $publisherScanner)
    {
        return new InitCommand('init', $publisherScanner);
    }

    public function testPHP()
    {
        $this->inputMock->expects($this->any())->method('getArgument')->will($this->returnCallback(function ($arg) {
            switch ($arg) {
                case 'initPath':
                    return TEST_PATH;
                case 'publisherType':
                    return 'php';
            }
        }));

        $this->testInstance->run($this->inputMock, $this->outputMock);

        $this->assertSame(
            $this->expectation,
            $this->publisherScanner->scan()->getData()
        );
    }

    public function testJson()
    {
        $this->inputMock->expects($this->any())->method('getArgument')->will($this->returnCallback(function ($arg) {
            switch ($arg) {
                case 'initPath':
                    return TEST_PATH;
                case 'publisherType':
                    return 'json';
            }
        }));

        $this->testInstance->run($this->inputMock, $this->outputMock);

        $this->assertSame(
            $this->expectation,
            $this->publisherScanner->scan()->getData()
        );
    }

    public function testYaml()
    {
        $this->inputMock->expects($this->any())->method('getArgument')->will($this->returnCallback(function ($arg) {
            switch ($arg) {
                case 'initPath':
                    return TEST_PATH;
                case 'publisherType':
                    return 'yaml';
            }
        }));

        $this->testInstance->run($this->inputMock, $this->outputMock);

        $this->assertSame(
            $this->expectation,
            $this->publisherScanner->scan()->getData()
        );
    }

    public function testYml()
    {
        $this->inputMock->expects($this->any())->method('getArgument')->will($this->returnCallback(function ($arg) {
            switch ($arg) {
                case 'initPath':
                    return TEST_PATH;
                case 'publisherType':
                    return 'yml';
            }
        }));

        $this->testInstance->run($this->inputMock, $this->outputMock);

        $this->assertSame(
            $this->expectation,
            $this->publisherScanner->scan()->getData()
        );
    }

    public function testBadFormat()
    {
        $this->inputMock->expects($this->any())->method('getArgument')->will($this->returnCallback(function ($arg) {
            switch ($arg) {
                case 'initPath':
                    return TEST_PATH;
                case 'publisherType':
                    return 'xml';
            }
        }));

        try {
            $this->testInstance->run($this->inputMock, $this->outputMock);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Exception);
        }
    }

    public function testBadInitPath()
    {
        $this->inputMock->expects($this->any())->method('getArgument')->will($this->returnCallback(function ($arg) {
            switch ($arg) {
                case 'initPath':
                    return TEST_PATH . '/fake';
                case 'publisherType':
                    return 'json';
            }
        }));

        try {
            $this->testInstance->run($this->inputMock, $this->outputMock);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Exception);
        }
    }
}
