<?php

namespace Cacher;

use Zend\Code\Reflection\ClassReflection;

class Cacher
{
    const STANDARD_PHP_OPEN_TAG = '<?php';

    /** @var array */
    protected $classes = [];
    /** @var IDriver */
    protected $adapter;
    /** @var Factory */
    protected $factory;
    /** @var CacheCodeGenerator */
    protected $generator;

    public function __construct(IDriver $adapter = null, Factory $factory = null, CacheCodeGenerator $generator = null)
    {
        $this->factory = $factory ?: new Factory();
        $this->adapter = $adapter ?: $this->factory->getNewZendDriver();
        $this->generator = $generator ?: $this->factory->getNewCacheCodeGenerator();
    }

    /**
     * Given an array of class files to cache, determine if it should be cached by the driver injected
     * into this object, if so continue to cache it's contents into a single cache file for better
     * loading performance.
     *
     * @param array
     * @return string
     */
    public function cache(array $classes)
    {
        $code = static::STANDARD_PHP_OPEN_TAG . PHP_EOL;
        foreach ($classes as $class) {
            try{
                $classReflection = $this->factory->getNewClassReflection($class);
            } catch (\ReflectionException $e) {
                // If class doesn't exist skip.
                continue;
            }
            if ($this->isNotAllowCaching($class, $classReflection)) {
                continue;
            }
            $this->classes[] = $class;
            $code .= $this->generator->getCacheCode($classReflection);
        }

        return $code;
    }

    /**
     * Determine if a class should not be cached.
     *
     * @param string $class
     * @param ClassReflection $classReflection
     * @return bool
     */
    protected function isNotAllowCaching($class, ClassReflection $classReflection)
    {
        return !$this->adapter->shouldCacheClass($classReflection)
            // Skip any classes we already know about
            || in_array($class, $this->classes)
            // Skip internal classes or classes from extensions
            || $classReflection->isInternal()
            || $classReflection->getExtensionName();
    }
}
