<?php

namespace Cacher;

use Zend\Code\Reflection\ClassReflection;

class Cacher
{
    protected $classes = array();

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
            if (in_array($class, $this->classes)) {
                continue;
            }
            $this->classes[] = $class;

            $class = new ClassReflection($class);

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

            $code .= static::getCacheCode($class);
        }

        return $code;
    }

    protected static function getCacheCode(ClassReflection $classReflection)
    {
        $useString = '';
        $usesNames = array();
        if (count($uses = $classReflection->getDeclaringFile()->getUses())) {
            foreach ($uses as $use) {
                $usesNames[$use['use']] = $use['as'];

                $useString .= "use {$use['use']}";

                if ($use['as']) {
                    $useString .= " as {$use['as']}";
                }

                $useString .= ";\n";
            }
        }

        $declaration = '';

        if ($classReflection->isAbstract() && !$classReflection->isInterface()) {
            $declaration .= 'abstract ';
        }

        if ($classReflection->isFinal()) {
            $declaration .= 'final ';
        }

        if ($classReflection->isInterface()) {
            $declaration .= 'interface ';
        }

        if (!$classReflection->isInterface()) {
            $declaration .= 'class ';
        }

        $declaration .= $classReflection->getShortName();

        $tmp = false;
        if (($parent = $classReflection->getParentClass()) && $classReflection->getNamespaceName()) {
            $tmp   = array_key_exists($parent->getName(), $usesNames)
                ? ($usesNames[$parent->getName()] ?: $parent->getShortName())
                : ((0 === strpos($parent->getName(), $classReflection->getNamespaceName()))
                    ? substr($parent->getName(), strlen($classReflection->getNamespaceName()) + 1)
                    : '\\' . $parent->getName());
        } else if ($parent && !$classReflection->getNamespaceName()) {
            $tmp = '\\' . $parent->getName();
        }

        if ($tmp) {
            $declaration .= " extends {$tmp}";
        }

        $int = array_diff($classReflection->getInterfaceNames(), $parent ? $parent->getInterfaceNames() : array());
        if (count($int)) {
            foreach ($int as $interface) {
                $iReflection = new ClassReflection($interface);
                $int  = array_diff($int, $iReflection->getInterfaceNames());
            }
            $declaration .= $classReflection->isInterface() ? ' extends ' : ' implements ';
            $declaration .= implode(', ', array_map(function($interface) use ($usesNames, $classReflection) {
                $iReflection = new ClassReflection($interface);
                return (array_key_exists($iReflection->getName(), $usesNames)
                    ? ($usesNames[$iReflection->getName()] ?: $iReflection->getShortName())
                    : ((0 === strpos($iReflection->getName(), $classReflection->getNamespaceName()))
                        ? substr($iReflection->getName(), strlen($classReflection->getNamespaceName()) + 1)
                        : '\\' . $iReflection->getName()));
            }, $int));
        }

        $contents = $classReflection->getContents(false);
        $dir  = dirname($classReflection->getFileName());
        $contents = trim(str_replace('__DIR__', sprintf("'%s'", $dir), $contents));

        $return = "\nnamespace "
            . $classReflection->getNamespaceName()
            . " {\n"
            . $useString
            . $declaration . "\n"
            . $contents
            . "\n}\n";

        return $return;
    }
}