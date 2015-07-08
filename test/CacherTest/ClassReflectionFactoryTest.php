<?php

namespace CacherTest;

use Cacher\ClassReflectionFactory;

class ClassReflectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOfClassReflectionReturned()
    {
        $factory = new ClassReflectionFactory();

        $result = $factory->factory('Cacher\ClassReflectionFactory');

        $this->assertInstanceOf('Zend\Code\Reflection\ClassReflection', $result);

        $this->assertEquals('Cacher\ClassReflectionFactory', $result->getName());
    }
}