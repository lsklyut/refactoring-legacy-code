<?php

namespace Cacher;

use Zend\Code\Reflection\ClassReflection;

class CacheCodeGenerator
{
    public function getCacheCode(ClassReflection $r)
    {
        $useString = $this->buildUseString($r);
        $usesNames = $this->buildUseNames($r);

        list($useString, $usesNames) = [
            $this->buildUseString($r),
            $this->buildUseNames($r)
        ];

        $declaration = $this->buildDeclareStatement($r, $usesNames);

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

    /**
     * @param ClassReflection $r
     * @return array
     */
    protected function buildUseNames(ClassReflection $r)
    {
        $usesNames = array();
        if (count($uses = $r->getDeclaringFile()->getUses())) {
            foreach ($uses as $use) {
                $usesNames[$use['use']] = $use['as'];
            }
            return $usesNames;
        }
        return $usesNames;
    }

    /**
     * @param ClassReflection $r
     * @return array
     */
    protected function buildUseString(ClassReflection $r)
    {
        $useString = '';
        if (count($uses = $r->getDeclaringFile()->getUses())) {
            foreach ($uses as $use) {
                $useString .= "use {$use['use']}";
                if ($use['as']) {
                    $useString .= " as {$use['as']}";
                }
                $useString .= ";\n";
            }
            return $useString;
        }
        return $useString;
    }

    /**
     * @param ClassReflection $r
     * @param $usesNames
     * @return string
     */
    protected function buildDeclareStatement(ClassReflection $r, $usesNames)
    {
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
            $tmp = array_key_exists($parent->getName(), $usesNames)
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
                $int = array_diff($int, $iReflection->getInterfaceNames());
            }
            $declaration .= $r->isInterface() ? ' extends ' : ' implements ';
            $declaration .= implode(', ', array_map(function ($interface) use ($usesNames, $r) {
                $iReflection = new ClassReflection($interface);
                return (array_key_exists($iReflection->getName(), $usesNames)
                    ? ($usesNames[$iReflection->getName()] ?: $iReflection->getShortName())
                    : ((0 === strpos($iReflection->getName(), $r->getNamespaceName()))
                        ? substr($iReflection->getName(), strlen($r->getNamespaceName()) + 1)
                        : '\\' . $iReflection->getName()));
            }, $int));
            return $declaration;
        }
        return $declaration;
    }
}