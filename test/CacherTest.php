<?php
class CacherTest extends PHPUnit_Framework_TestCase
{
    public function testCanDoSomething()
    {
        $cacher = new \Cacher\Cacher();
        $cacher->cache(['Zend\Stdlib\Message']);

        $ref = $this->getMock('ClassReflection');

        $this->assertTrue(true);
    }
}