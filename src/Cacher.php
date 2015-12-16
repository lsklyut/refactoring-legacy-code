<?php

namespace Cacher;

use Zend\Code\Reflection\ClassReflection;

class Cacher
{
    protected $classes = array();
    protected $adapter;

    public function __construct(IDriver $driver = null)
    {
        $this->adapter = $driver ?: new ZendDriver();
    }

    public function cache($classes)
    {
        $code = "<?php\n";

        foreach ($classes as $class) {
            $classReflection = new ClassReflection($class);
            $shouldCacheClass = $this->adapter->shouldCacheClass($classReflection);

            if (!$shouldCacheClass) {
                continue;
            }

            // Skip any classes we already know about
            if (in_array($class, $this->classes)) {
                continue;
            }

            // Skip internal classes or classes from extensions
            // (this shouldn't happen, as we're only caching Zend classes)
            if ($classReflection->isInternal()
                || $classReflection->getExtensionName()
            ) {
                continue;
            }

            $this->classes[] = $class;
            $code .= (new CacheCodeGenerator())->getCacheCode($classReflection);
        }

        return $code;
    }
}