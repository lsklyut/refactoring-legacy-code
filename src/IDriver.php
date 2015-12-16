<?php

namespace Cacher;

use Zend\Code\Reflection\ClassReflection;

interface IDriver
{
    /**
     * @param ClassReflection $classReflection
     * @return bool
     */
    public function shouldCacheClass(ClassReflection $classReflection);
}