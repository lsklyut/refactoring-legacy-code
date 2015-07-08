<?php

namespace Cacher;

use Zend\Code\Reflection\ClassReflection;

class ClassReflectionFactory
{
    public function factory($class)
    {
        return new ClassReflection($class);
    }
}
