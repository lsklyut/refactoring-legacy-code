<?php

namespace Cacher;

use Zend\Code\Reflection\ClassReflection;

class Cacher
{
    protected $classes = array();

    public function cache($c)
    {
        $code = "<?php\n";

        foreach ($c as $ct) {
            // Skip non-Zend classes
            if (0 !== strpos($ct, 'Zend')) {
                continue;
            }

            // Skip the autoloader factory and this class
            if (in_array($ct, array('Zend\Loader\AutoloaderFactory', __CLASS__))) {
                continue;
            }

            if ($ct === 'Zend\Loader\SplAutoloader') {
                continue;
            }

            // Skip any classes we already know about
            if (in_array($ct, $this->classes)) {
                continue;
            }
            $this->classes[] = $ct;

            $ct = new ClassReflection($ct);

            // Skip ZF2-based autoloaders
            if (in_array('Zend\Loader\SplAutoloader', $ct->getInterfaceNames())) {
                continue;
            }

            // Skip internal classes or classes from extensions
            // (this shouldn't happen, as we're only caching Zend classes)
            if ($ct->isInternal()
                || $ct->getExtensionName()
            ) {
                continue;
            }

            $code .= static::getCacheCode($ct);
        }

        return $code;
    }

    protected static function getCacheCode(ClassReflection $r)
    {
        $useString = '';
        $usesNames = array();
        if (count($uses = $r->getDeclaringFile()->getUses())) {
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

        if ($r->isAbstract() && !$r->isInterface()) {
            $declaration .= 'abstract ';
        }

//        if (!$r->isAbstract() && $r->isInterface()) {
//            $declaration .= 'interface ';
//        }

        if ($r->isFinal()) {
            $declaration .= 'final ';
        }

        if ($r->isInterface()) {
            $declaration .= 'interface ';
        }

        if (!$r->isInterface()) {
            $declaration .= 'class ';
        }

        $declaration .= $r->getShortName();

        $tmp = false;
        $parent = $r->getParentClass();
        if (!$r->getNamespaceName()) {
            $tmp = '\\' . $parent->getName();
        }

        $tmp = false;
        if (($parent = $r->getParentClass()) && $r->getNamespaceName()) {
            $tmp   = array_key_exists($parent->getName(), $usesNames)
                ? ($usesNames[$parent->getName()] ?: $parent->getShortName())
                : ((0 === strpos($parent->getName(), $r->getNamespaceName()))
                    ? substr($parent->getName(), strlen($r->getNamespaceName()) + 1)
                    : '\\' . $parent->getName());
        } else if ($parent && !$r->getNamespaceName()) {
            $tmp = '\\' . $parent->getName();
        }

        if ($tmp) {
            $declaration .= " extends {$tmp}";
        }

        $int = array_diff($r->getInterfaceNames(), $parent ? $parent->getInterfaceNames() : array());
        if (count($int)) {
            foreach ($int as $interface) {
                $iReflection = new ClassReflection($interface);
                $int  = array_diff($int, $iReflection->getInterfaceNames());
            }
            $declaration .= $r->isInterface() ? ' extends ' : ' implements ';
            $declaration .= implode(', ', array_map(function($interface) use ($usesNames, $r) {
                $iReflection = new ClassReflection($interface);
                return (array_key_exists($iReflection->getName(), $usesNames)
                    ? ($usesNames[$iReflection->getName()] ?: $iReflection->getShortName())
                    : ((0 === strpos($iReflection->getName(), $r->getNamespaceName()))
                        ? substr($iReflection->getName(), strlen($r->getNamespaceName()) + 1)
                        : '\\' . $iReflection->getName()));
            }, $int));
        }

        $contents = $r->getContents(false);
        $dir  = dirname($r->getFileName());
        $contents = trim(str_replace('__DIR__', sprintf("'%s'", $dir), $contents));

        $return = "\nnamespace "
            . $r->getNamespaceName()
            . " {\n"
            . $useString
            . $declaration . "\n"
            . $contents
            . "\n}\n";

        return $return;
    }
}