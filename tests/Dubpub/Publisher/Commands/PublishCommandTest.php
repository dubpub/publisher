<?php
/**
 * Created by PhpStorm.
 * User: madman
 * Date: 12.11.15
 * Time: 1:02
 */

namespace Dubpub\Publisher\Commands;

use Dubpub\Publisher\PublisherScanner;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

class PublishCommandTest extends \HelperTest
{
    /**
     *
     * @param PublisherScanner $publisherScanner
     * @return Command
     */
    public function getCommand(PublisherScanner $publisherScanner)
    {
        return new PublishCommand('publish', $publisherScanner, new Filesystem());
    }

    protected function initiate($type)
    {
        $command = new InitCommand('init', $this->publisherScanner);

        $inputMock = $this->getMock(InputInterface::class);

        $inputMock->expects($this->any())->method('getArgument')->will($this->returnCallback(function ($argument) use ($type) {
            switch ($argument) {
                case 'initPath':
                    return TEST_PATH;
                case 'publisherType':
                    return $type;
            }
        }));

        $command->run($inputMock, $this->outputMock);

        $this->assertSame(
            $this->expectation,
            $this->publisherScanner->scan()->getData()
        );
    }

    protected function prepareInput($package = '*', $group = '*')
    {
        $this->inputMock->expects($this->any())->method('getArgument')
            ->will($this->returnCallback(function ($arg) use ($package, $group) {
                switch ($arg) {
                    case 'package':
                        return $package;
                    case 'group':
                        return $group;
                    default:
                        var_dump($arg) || die;
                }
            }));

        $this->inputMock->expects($this->any())->method('getOption')->will($this->returnCallback(function ($opt) {
            switch ($opt) {
                case 'configPath':
                    return TEST_PATH;
                case 'publishPath':
                    return TEST_PATH;
                default:
                    var_dump($opt) || die;
            }
        }));
    }

    public function testPHPSuccessPublish()
    {
        $this->initiate('php');

        $this->prepareInput();

        $this->testInstance->run($this->inputMock, $this->outputMock);
    }

    public function testJSON()
    {
        $this->initiate('json');

        $this->prepareInput();

        $this->testInstance->run($this->inputMock, $this->outputMock);
    }

    public function testYAML()
    {
        $this->initiate('yaml');

        $this->prepareInput();

        $this->testInstance->run($this->inputMock, $this->outputMock);
    }

    public function testYML()
    {
        $this->initiate('yml');

        $this->prepareInput();

        $this->testInstance->run($this->inputMock, $this->outputMock);
    }

    public function testNoVendorFolder()
    {
        $this->initiate('yml');

        $filesystem = new Filesystem();

        $this->prepareInput();

        $filesystem->move(TEST_PATH . '/vendor', TEST_PATH . '/vendor1');

        try {
            $this->testInstance->run($this->inputMock, $this->outputMock);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Exception);
        }

        $filesystem->move(TEST_PATH . '/vendor1', TEST_PATH . '/vendor');
    }

    public function testNoPublisher()
    {
        $this->prepareInput();

        try {
            $this->testInstance->run($this->inputMock, $this->outputMock);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Exception);
        }
    }

    public function testSpecific()
    {
        $this->initiate('php');

        $this->prepareInput('dubpub/publisher,vendor/package');

        $this->testInstance->run($this->inputMock, $this->outputMock);
    }

    public function testSpecificGroup()
    {
        $this->initiate('php');

        $this->prepareInput('dubpub/publisher,vendor/package', 'assets,links');

        $this->testInstance->run($this->inputMock, $this->outputMock);
    }

    public function testOverwrite()
    {
        $this->initiate('php');

        $this->prepareInput();

        $this->testInstance->run($this->inputMock, $this->outputMock);
        $this->testInstance->run($this->inputMock, $this->outputMock);
    }

    public function testFailGlobals()
    {
        $this->initiate('php');

        $this->prepareInput('dubpub/publisher');

        $handler = $this->publisherScanner->scan(TEST_PATH);

        $handler->setPackageGroups('dubpub/publisher', ['assets' => ['assets -> /absolute']]);

        $handler->write();

        try {
            $this->testInstance->run($this->inputMock, $this->outputMock);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Exception);
        }

        $handler->setPackageGroups('dubpub/publisher', ['assets' => ['/assets']]);

        $handler->write();

        try {
            $this->testInstance->run($this->inputMock, $this->outputMock);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Exception);
        }

        $handler->setPackageGroups('dubpub/publisher', ['assets' => ['/assets -> /assets']]);

        $handler->write();

        try {
            $this->testInstance->run($this->inputMock, $this->outputMock);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Exception);
        }

        $handler->setPackageGroups('dubpub/publisher', ['assets' => ['assets']]);

        $handler->write();

        $this->testInstance->run($this->inputMock, $this->outputMock);


        $handler->setPackageGroups('dubpub/publisher', ['assets' => ['assets -> / ->absolute']]);

        $handler->write();

        try {
            $this->testInstance->run($this->inputMock, $this->outputMock);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Exception);
        }
    }
}
