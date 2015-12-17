<?php

namespace Cacher;

use Zend\Code\Reflection\ClassReflection;

/**
 * @codeCoverageIgnore
 * Factory classes shouldn't be covered, because its sole purpose is to abstract out
 * instantiating new instances of classes.
 */
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
