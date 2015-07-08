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

    public function __construct(array $allowedNamespaces = null)
    {
        if (null !== $allowedNamespaces) {
            $this->allowedNamespaces = $allowedNamespaces;
        }
    }

    public function cache($classes)
    {
        $code = "<?php\n";

        foreach ($classes as $class) {
            if ($this->classNamespaceAllowedToCache($class)) {
                continue;
            }

            // Skip the autoloader factory and this class
            if (in_array($class, array('Zend\Loader\AutoloaderFactory', __CLASS__))) {
                continue;
            }

            if ($class === 'Zend\Loader\SplAutoloader') {
                continue;
            }

            // Skip any classes we already know about
            if (in_array($class, $this->loadedClasses)) {
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
}