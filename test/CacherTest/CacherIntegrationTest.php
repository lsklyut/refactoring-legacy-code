<?php

namespace CacherTest;

use Cacher\Cacher;
use Zend\EventManager\Filter\FilterIterator;
use Zend\Stdlib\ArrayObject;
use Zend\Stdlib\Message;

class CacherIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testMultipleClasses()
    {
        $cacher = new Cacher();

        $message = new Message();

        $arrayObject = new ArrayObject();

        $filetIterator = new FilterIterator();

        $classes = get_declared_classes();

        $actual = $cacher->cache($classes);

        $expected = file_get_contents('test/data/testMultipleClasses_original.txt');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @runInSeparateProcess
     */
    public function testNoClassesReturnsEmptyFile()
    {
        $cacher = new Cacher();

        $classes = get_declared_classes();

        $actual = $cacher->cache($classes);

        $expected = file_get_contents('test/data/testNoClassesReturnsEmptyFile_original.txt');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @runInSeparateProcess
     */
    public function testOneClass()
    {
        $cacher = new Cacher();

        $message = new \Zend\Stdlib\Message();

        $classes = get_declared_classes();

        $actual = $cacher->cache($classes);

        $expected = file_get_contents('test/data/testOneClass_original.txt');

        $this->assertEquals($expected, $actual);
    }
}