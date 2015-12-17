<?php

require_once('Base.php');

class CacherDriverTest extends Base
{
    /**
     * @return array
     */
    public function providerShouldCacheClass()
    {
        return [
            // Case: cache-able class
            ['Zend\Stdlib\Message', true],
            // Case: ZendDriver class is not cache-able
            ['Cacher\ZendDriver', false],
            // Case: Zend loader class is not cache-able
            ['Zend\Loader\AutoloaderFactory', false],
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
     * @dataProvider providerShouldCacheClass
     * @param array
     * @param bool
     * @param bool
     * @param string
     */
    public function testShouldCacheClass($class, $expect)
    {
        $driver = new \Cacher\ZendDriver();
        $reflection = $this->getMock('Zend\Code\Reflection\ClassReflection', ['getName', 'getInterfaceNames'], [], '', false);
        $reflection->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($class));
        $reflection->expects($this->any())
            ->method('getInterfaceNames')
            ->will($this->returnValue([]));
        $this->assertSame($expect, $driver->shouldCacheClass($reflection));
    }
}
