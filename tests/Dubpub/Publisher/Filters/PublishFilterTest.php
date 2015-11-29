<?php namespace Dubpub\Publisher\Filters;

use Dubpub\Publisher\Exceptions\InvalidConfigPathOptionException;
use Dubpub\Publisher\Exceptions\InvalidGroupArgumentException;
use Dubpub\Publisher\Exceptions\InvalidPackageArgumentException;
use Dubpub\Publisher\Exceptions\InvalidPublishPathOptionException;
use Symfony\Component\Console\Input\InputInterface;
use PHPUnit_Framework_MockObject_MockObject as Mock;


class PublishFilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InputInterface|Mock
     */
    protected $inputMock;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     */
    protected function setUp()
    {
        $this->inputMock = $this->getMock(InputInterface::class);
    }

    protected function prepareArguments($package = '*', $group = '*') {
        $this->inputMock->expects($this->any())->method('getArgument')
            ->will($this->returnCallback(function ($arg) use ($package, $group) {
                switch ($arg) {
                    case 'package':
                        return $package;
                    case 'group':
                        return $group;
                }
            }));
    }

    protected function prepareOptions($publish = '', $config = '') {
        $this->inputMock->expects($this->any())->method('getOption')
            ->will($this->returnCallback(function ($arg) use ($publish, $config) {
                switch ($arg) {
                    case 'publishPath':
                        return $publish;
                    case 'configPath':
                        return $config;
                }
            }));
    }

    public function testPackageSuccessAll()
    {
        $this->prepareOptions();

        $this->prepareArguments();

        $filter = new PublishFilter($this->inputMock);

        $filter->process();
    }

    public function testPackageSuccessOne()
    {
        $this->prepareOptions();

        $this->prepareArguments('vendor/package');

        $filter = new PublishFilter($this->inputMock);

        $filter->process();
    }

    public function testPackageFailOne()
    {
        $this->prepareOptions();

        $this->prepareArguments('vendorpackage');

        $filter = new PublishFilter($this->inputMock);

        $this->setExpectedException(InvalidPackageArgumentException::class);

        $filter->process();
    }

    public function testPackageSuccessMany()
    {
        $this->prepareOptions();

        $this->prepareArguments('vendor/package, vendor1/package1');

        $filter = new PublishFilter($this->inputMock);

        $filter->process();
    }

    public function testPackageFailMany()
    {
        $this->prepareOptions();

        $this->prepareArguments('vendor/package,vendorpackage');

        $filter = new PublishFilter($this->inputMock);

        $this->setExpectedException(InvalidPackageArgumentException::class);

        $filter->process();
    }

    public function testGroupSuccessAll()
    {
        $this->prepareOptions();

        $this->prepareArguments();

        $filter = new PublishFilter($this->inputMock);

        $filter->process();
    }

    public function testGroupSuccessOne()
    {
        $this->prepareOptions();

        $this->prepareArguments('*', 'asd');

        $filter = new PublishFilter($this->inputMock);

        $filter->process();
    }

    public function testGroupSuccessMany()
    {
        $this->prepareOptions();

        $this->prepareArguments('*', 'vendor-package,vendor1-package1');

        $filter = new PublishFilter($this->inputMock);

        $filter->process();
    }

    public function testGroupFailMany()
    {
        $this->prepareOptions();

        $this->prepareArguments('*', '*,kj');

        $filter = new PublishFilter($this->inputMock);

        $this->setExpectedException(InvalidGroupArgumentException::class);

        $filter->process();
    }

    public function testFailPublishPath()
    {
        $this->prepareArguments();

        $this->prepareOptions(uniqid());

        $filter = new PublishFilter($this->inputMock);

        $this->setExpectedException(InvalidPublishPathOptionException::class);

        $filter->process();
    }

    public function testFailOptionPath()
    {
        $this->prepareArguments();

        $this->prepareOptions('./', uniqid());

        $filter = new PublishFilter($this->inputMock);

        $this->setExpectedException(InvalidConfigPathOptionException::class);

        $filter->process();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        unset($this->inputMock);
    }
}
