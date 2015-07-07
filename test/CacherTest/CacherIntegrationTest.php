<?php

namespace CacherTest;

use Cacher\Cacher;
use Zend\EventManager\Filter\FilterIterator;
use Zend\Stdlib\ArrayObject;
use Zend\Stdlib\Message;

class CacherIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Cacher
     */
    protected $sut;

    public function setUp()
    {
        $this->sut = new Cacher();
    }

    /**
     * @runInSeparateProcess
     */
    public function testMultipleClasses()
    {
        $message = new Message();

        $arrayObject = new ArrayObject();

        $filetIterator = new FilterIterator();

        $classes = get_declared_classes();

        $actual = $this->sut->cache($classes);

        $expected = $this->getTestFileContents('testMultipleClasses');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @runInSeparateProcess
     */
    public function testNoClassesReturnsEmptyFile()
    {
        $classes = get_declared_classes();

        $actual = $this->sut->cache($classes);

        $expected = $this->getTestFileContents('testNoClassesReturnsEmptyFile');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @runInSeparateProcess
     */
    public function testOneClass()
    {
        $message = new \Zend\Stdlib\Message();

        $classes = get_declared_classes();

        $actual = $this->sut->cache($classes);

        $expected = $this->getTestFileContents('testOneClass');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return string
     */
    public function getTestFileContents($testFile)
    {
        return file_get_contents(__DIR__ . '/../data/' . $testFile . '_original.txt');
    }
}