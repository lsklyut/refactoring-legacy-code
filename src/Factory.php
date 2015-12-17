<?php

namespace Cacher;

use Zend\Code\Reflection\ClassReflection;

class Factory
{
    /**
     * @param string $class
     * @return ClassReflection
     */
    public function getNewClassReflection($class)
    {
        return new ClassReflection($class);
    }

    /**
     * @return ClassReflection
     */
    public function getNewZendDriver()
    {
        return new ZendDriver();
    }

    /**
     * @return CacheCodeGenerator
     */
    public function getNewCacheCodeGenerator()
    {
        return new CacheCodeGenerator();
    }
}
