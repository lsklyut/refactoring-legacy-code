<?php

namespace Cacher;

class Cacher
{
    protected $loadedClasses = array();

    /**
     * @var ClassReflectionFactory
     */
    protected $classReflectionFactory;

    /**
     * @var CacheCodeGenerator
     */
    protected $cacheCodeGenerator;

    protected $allowedNamespaces = array('Zend');

    protected $ignoredClasses = array(
        'Zend\Loader\AutoloaderFactory',
        'Zend\Loader\SplAutoloader'
    );

    public function __construct(array $allowedNamespaces = null, array $ignoredClasses = null)
    {
        if (null !== $allowedNamespaces) {
            $this->allowedNamespaces = $allowedNamespaces;
        }

        if (null !== $ignoredClasses) {
            $this->ignoredClasses = $ignoredClasses;
        }
    }

    public function cache($classes)
    {
        $code = "<?php\n";

        foreach ($classes as $class) {
            if ($this->classNamespaceAllowedToCache($class)) {
                continue;
            }

            if ($this->shouldIgnoreClass($class)) {
                continue;
            }

            $this->loadedClasses[] = $class;

            $class = $this->getClassReflectionFactory()->factory($class);

            // Skip ZF2-based autoloaders
            if (in_array('Zend\Loader\SplAutoloader', $class->getInterfaceNames())) {
                continue;
            }

            // Skip internal classes or classes from extensions
            // (this shouldn't happen, as we're only caching Zend classes)
            if ($class->isInternal()
                || $class->getExtensionName()
            ) {
                continue;
            }

            $code .= $this->getCacheCodeGenerator()->generate($class);
        }

        return $code;
    }

    /**
     * @return ClassReflectionFactory
     */
    public function getClassReflectionFactory()
    {
        if (null === $this->classReflectionFactory) {
            $this->classReflectionFactory = new ClassReflectionFactory();
        }

        return $this->classReflectionFactory;
    }

    /**
     * @param ClassReflectionFactory $classReflectionFactory
     */
    public function setClassReflectionFactory($classReflectionFactory)
    {
        $this->classReflectionFactory = $classReflectionFactory;
    }

    /**
     * @return CacheCodeGenerator
     */
    public function getCacheCodeGenerator()
    {
        if (null === $this->cacheCodeGenerator) {
            $this->cacheCodeGenerator = new CacheCodeGenerator();
        }

        return $this->cacheCodeGenerator;
    }

    /**
     * @param CacheCodeGenerator $cacheCodeGenerator
     */
    public function setCacheCodeGenerator($cacheCodeGenerator)
    {
        $this->cacheCodeGenerator = $cacheCodeGenerator;
    }

    /**
     * @param $class
     * @return bool
     */
    protected function classNamespaceAllowedToCache($class)
    {
        foreach ($this->allowedNamespaces as $namespace) {
            if (0 === strpos($class, $namespace)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $class
     * @return bool
     */
    private function shouldIgnoreClass($class)
    {
        $ignoredClasses = array_merge($this->loadedClasses, $this->ignoredClasses, [__CLASS__]);

        return in_array($class, $ignoredClasses);
    }
}