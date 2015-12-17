<?php

namespace Cacher;

use Zend\Code\Reflection\ClassReflection;

class ZendDriver implements IDriver
{
    const PREFIX = 'Zend';
    const AUTOLOADER_FACTORY = 'Zend\Loader\AutoloaderFactory';
    const SPL_AUTOLOADER = 'Zend\Loader\SplAutoloader';

    /**
     * @param ClassReflection $classReflection
     * @return bool
     */
    public function shouldCacheClass(ClassReflection $classReflection)
    {
        $class = $classReflection->getName();
        // Include only Zend classes
        return strpos($class, static::PREFIX) === 0
            // Skip the autoloader factory and this class
            && !in_array($class, [static::AUTOLOADER_FACTORY, __CLASS__])
            // Skip Zend SPL Autoloader
            && $class !== static::SPL_AUTOLOADER
            // Skip ZF2-based autoloaders
            && !in_array(static::SPL_AUTOLOADER, $classReflection->getInterfaceNames());
    }
}
