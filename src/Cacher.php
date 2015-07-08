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

    public function __construct(ClassReflectionFactory $classReflectionFactory = null, CacheCodeGenerator $cacheCodeGenerator = null)
    {
        if (null === $classReflectionFactory) {
            $classReflectionFactory = new ClassReflectionFactory();
        }

        $this->classReflectionFactory = $classReflectionFactory;

        if (null === $cacheCodeGenerator) {
            $cacheCodeGenerator = new CacheCodeGenerator();
        }

        $this->cacheCodeGenerator = $cacheCodeGenerator;
    }

    public function cache($classes)
    {
        $code = "<?php\n";

        foreach ($classes as $class) {
            // Skip non-Zend classes
            if (0 !== strpos($class, 'Zend')) {
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

            $class = $this->classReflectionFactory->factory($class);

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

            $code .= $this->cacheCodeGenerator->generate($class);
        }

        return $code;
    }




}