<?php

namespace CacherTest;

use Cacher\CacheCodeGenerator;
use Cacher\Cacher;
use Cacher\ClassReflectionFactory;
use Prophecy\Prophecy\ObjectProphecy;
use Zend\Code\Reflection\ClassReflection;
use Zend\EventManager\Filter\FilterIterator;
use Zend\Stdlib\ArrayObject;
use Zend\Stdlib\Hydrator\NamingStrategy\ArrayMapNamingStrategy;
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

        $namingStrategy = new ArrayMapNamingStrategy([]);

        $classes = array_merge(get_declared_interfaces(), get_declared_classes());

        $actual = $this->sut->cache($classes);

        $expected = $this->getTestFileContents('testMessageArrayObjectAndFilterIteratorClasses');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @runInSeparateProcess
     */
    public function testNoClassesReturnsEmptyFile()
    {
        $classes = array_merge(get_declared_interfaces(), get_declared_classes());

        $actual = $this->sut->cache($classes);

        $expected = $this->getTestFileContents('testNoClasses');

        $this->assertEquals($expected, $actual);
    }

    /**
     * @runInSeparateProcess
     */
    public function testOneClass()
    {
        $message = new \Zend\Stdlib\Message();

        $classes = array_merge(get_declared_interfaces(), get_declared_classes());

        $actual = $this->sut->cache($classes);

        $expected = $this->getTestFileContents('testMessageClass');

        $this->assertEquals($expected, $actual);
    }

    public function testSkipsZendAutoloaders()
    {
        $classes = array('Zend\Loader\AutoloaderFactory', 'Zend\Loader\SplAutoloader');

        $actual = $this->sut->cache($classes);

        $expected = $this->getTestFileContents('testNoClasses');

        $this->assertEquals($expected, $actual);
    }

    public function testSkipsSameClassOnSecondPass()
    {
        $classes = array('Zend\Stdlib\MessageInterface', 'Zend\Stdlib\Message', 'Zend\Stdlib\Message');

        $actual = $this->sut->cache($classes);

        $expected = $this->getTestFileContents('testMessageClass');

        $this->assertEquals($expected, $actual);
    }

    public function testSkipsInternalClasses()
    {
        /** @var ObjectProphecy|ClassReflection $mockClassReflection */
        $mockClassReflection = $this->prophesize('Zend\Code\Reflection\ClassReflection');

        $mockClassReflection->isInternal()->willReturn(true);
        $mockClassReflection->getInterfaceNames()->willReturn([]);

        /** @var ObjectProphecy|ClassReflectionFactory $mockClassReflectionFactory */
        $mockClassReflectionFactory = $this->prophesize('Cacher\ClassReflectionFactory');

        $internalClass = 'Zend\InternalClass';

        $mockClassReflectionFactory->factory($internalClass)->willReturn($mockClassReflection->reveal());

        /** @var ObjectProphecy|CacheCodeGenerator $mockCacheCodeGenerator */
        $mockCacheCodeGenerator = $this->prophesize('Cacher\CacheCodeGenerator');

        $cacher = new Cacher();
        $cacher->setClassReflectionFactory($mockClassReflectionFactory->reveal());
        $cacher->setCacheCodeGenerator($mockCacheCodeGenerator->reveal());

        $actual = $cacher->cache([$internalClass]);

        $this->assertEquals("<?php\n", $actual);
    }

    public function testCanCacheNonZendFiles()
    {
        /** @var ObjectProphecy|ClassReflection $mockClassReflection */
        $mockClassReflection = $this->prophesize('Zend\Code\Reflection\ClassReflection');

        $mockClassReflection->isInternal()->willReturn(false);
        $mockClassReflection->getInterfaceNames()->willReturn([]);
        $mockClassReflection->getExtensionName()->willReturn('');

        /** @var ObjectProphecy|ClassReflectionFactory $mockClassReflectionFactory */
        $mockClassReflectionFactory = $this->prophesize('Cacher\ClassReflectionFactory');

        $cacheCodeGeneratorClass = 'Cacher\CacheCodeGenerator';

        $mockClassReflectionFactory->factory($cacheCodeGeneratorClass)->willReturn($mockClassReflection->reveal());

        /** @var ObjectProphecy|CacheCodeGenerator $mockCacheCodeGenerator */
        $mockCacheCodeGenerator = $this->prophesize('Cacher\CacheCodeGenerator');

        $mockCacheCodeGenerator->generate($mockClassReflection)->willReturn('CacheCodeGenerator');

        $cacher = new Cacher(['Cacher']);

        $actual = $cacher->cache([$cacheCodeGeneratorClass]);

        $this->assertTrue(false !== strpos($actual, 'CacheCodeGenerator'), 'CacheCodeGenerator was skipped!');
    }

    /**
     * @return string
     */
    public function getTestFileContents($testFile)
    {
        return file_get_contents(__DIR__ . '/../data/' . $testFile . '_original.txt');
    }
}