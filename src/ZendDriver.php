<?php

namespace Cacher;

use Zend\Code\Reflection\ClassReflection;

class ZendDriver implements IDriver
{
    /**
     * @param ClassReflection $classReflection
     * @return bool
     */
    public function shouldCacheClass(ClassReflection $classReflection)
    {
        $class = $classReflection->getName();
        $shouldCacheClass = true;

        // Skip non-Zend classes
        if (0 !== strpos($class, 'Zend')) {
            $shouldCacheClass = false;
        }

        // Skip the autoloader factory and this class
        if (in_array($class, array('Zend\Loader\AutoloaderFactory', __CLASS__))) {
            $shouldCacheClass = false;
        }

        if ($class === 'Zend\Loader\SplAutoloader') {
            $shouldCacheClass = false;
        }

        // Skip ZF2-based autoloaders
        if (in_array('Zend\Loader\SplAutoloader', $classReflection->getInterfaceNames())) {
            $shouldCacheClass = false;
        }

        return $shouldCacheClass;
    }
}