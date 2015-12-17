<?php

require_once('Base.php');

class CacherTest extends Base
{
    /**
     * @return array
     */
    public function providerCache()
    {
        return [
            // Case: Valid, allowable class to be cached
            [['Zend\Stdlib\Message'], false, false, 'namespace Zend\Stdlib {'],
            // Case: Class doesn't exits
            [['Zend\Foo\Bar'], true, true, 'namespace Zend\Foo {'],
            // Case: Class is not allowable
            [['Zend\Stdlib\Message'], true, false, 'namespace Zend\Stdlib {'],
        ];
    }

    /**
     * Scenario: Cache an array of classes or interfaces
     * Given an array of classes or interfaces
     * When caching classes or interfaces
     * Then for each class or interface in the array generate a block
     * of namespace containing the content of the class into one giant string to be cached,
     * only if the class is allow caching.
     *
     * @dataProvider providerCache
     * @param array
     * @param bool
     * @param bool
     * @param string
     */
    public function testCache(array $classes, $isNotAllowed, $isException, $expect)
    {
        $cacher = $this->getMock('\Cacher\Cacher', ['isNotAllowCaching']);
        $cacher->expects($isException ? $this->never(): $this->once())
            ->method('isNotAllowCaching')
            ->will($this->returnValue($isNotAllowed));
        if ($isNotAllowed) {
            $this->assertNotContains($expect, $cacher->cache($classes));
        } else {
            $this->assertContains($expect, $cacher->cache($classes));
        }
    }

    /**
     * Scenario: Determine when a class file will not allow caching.
     * Given a class
     * And a  reflection object of the class
     * When determining if a class is not allow caching
     * Then if concrete method IDriver::shouldCacheClass implemented in a concrete class driver class
     * such as ZendDriver, should return false or the reflection for the class is internal,
     * or already exists in the cache classes, or it has an extension, then allow not allow caching will true, otherwise false.
     */
    public function testIsNotAllowCaching()
    {
        $class = 'Zend\Stdlib\Message';
        $reflection = $this->getMock('Zend\Code\Reflection\ClassReflection', ['isInternal', 'getExtensionName'], [], '', false);
        $reflection->expects($this->once())
            ->method('isInternal')
            ->will($this->returnValue(false));
        $reflection->expects($this->once())
            ->method('getExtensionName')
            ->will($this->returnValue(null));

        $driver = $this->getMock('\Cacher\ZendDriver', ['shouldCacheClass']);
        $driver->expects($this->once())
            ->method('shouldCacheClass')
            ->will($this->returnValue(true));

        $factory = $this->getMock('\Cacher\Factory', ['getNewZendDriver']);
        $factory->expects($this->once())
            ->method('getNewZendDriver')
            ->will($this->returnValue($driver));

        $cacher =  new \Cacher\Cacher(null, $factory);
        $this->assertSame(false, $this->invokeRestrictedMethod($cacher, 'isNotAllowCaching', [$class, $reflection]));
    }
}
