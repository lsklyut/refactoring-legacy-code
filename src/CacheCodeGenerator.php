<?php

namespace Cacher;

use Zend\Code\Reflection\ClassReflection;

class CacheCodeGenerator
{
    /**
     * @param ClassReflection $classReflection
     * @return string
     */
    public function getCacheCode(ClassReflection $classReflection)
    {
        $useString = $this->buildUseString($classReflection);
        $usesNames = $this->buildUseNames($classReflection);
        $declaration = $this->buildDeclareStatement($classReflection, $usesNames);
        $contents = $classReflection->getContents(false);
        $directory = dirname($classReflection->getFileName());
        $contents = trim(str_replace(__DIR__, sprintf("'%s'", $directory), $contents));

        return PHP_EOL
            . 'namespace '
            . $classReflection->getNamespaceName()
            . ' {'
            . PHP_EOL
            . $useString
            . $declaration
            . PHP_EOL
            . $contents
            . PHP_EOL
            . '}'
            . PHP_EOL;
    }

    /**
     * @param ClassReflection $classReflection
     * @return array
     */
    protected function buildUseNames(ClassReflection $classReflection)
    {
        $usesNames = [];
        if (count($uses = $classReflection->getDeclaringFile()->getUses())) {
            foreach ($uses as $use) {
                $usesNames[$use['use']] = $use['as'];
            }
            return $usesNames;
        }
        return $usesNames;
    }

    /**
     * @param ClassReflection $classReflection
     * @return array
     */
    protected function buildUseString(ClassReflection $classReflection)
    {
        $useString = '';
        if (count($uses = $classReflection->getDeclaringFile()->getUses())) {
            foreach ($uses as $use) {
                $useString .= "use {$use['use']}";
                if ($use['as']) {
                    $useString .= " as {$use['as']}";
                }
                $useString .= ';' . PHP_EOL;
            }
            return $useString;
        }
        return $useString;
    }

    /**
     * @param ClassReflection $classReflection
     * @param array $usesNames
     * @return string
     */
    protected function buildDeclareStatement(ClassReflection $classReflection, array $usesNames)
    {
        $declaration = $this->getStartStatement($classReflection);
        $tmp = false;
        if (($parent = $classReflection->getParentClass()) && $classReflection->getNamespaceName()) {
            $tmp = array_key_exists($parent->getName(), $usesNames)
                ? ($usesNames[$parent->getName()] ?: $parent->getShortName())
                : ((0 === strpos($parent->getName(), $classReflection->getNamespaceName()))
                    ? substr($parent->getName(), strlen($classReflection->getNamespaceName()) + 1)
                    : '\\' . $parent->getName());
        } elseif ($parent && !$classReflection->getNamespaceName()) {
            $tmp = '\\' . $parent->getName();
        }

        if ($tmp) {
            $declaration .= " extends {$tmp}";
        }

        $int = array_diff($classReflection->getInterfaceNames(), $parent ? $parent->getInterfaceNames() : []);
        if (count($int)) {
            foreach ($int as $interface) {
                $iReflection = new ClassReflection($interface);
                $int = array_diff($int, $iReflection->getInterfaceNames());
            }
            $declaration .= $classReflection->isInterface() ? ' extends ' : ' implements ';
            $declaration .= implode(', ', array_map(function ($interface) use ($usesNames, $classReflection) {
                $iReflection = new ClassReflection($interface);
                return (array_key_exists($iReflection->getName(), $usesNames)
                    ? ($usesNames[$iReflection->getName()] ?: $iReflection->getShortName())
                    : ((0 === strpos($iReflection->getName(), $classReflection->getNamespaceName()))
                        ? substr($iReflection->getName(), strlen($classReflection->getNamespaceName()) + 1)
                        : '\\' . $iReflection->getName()));
            }, $int));
            return $declaration;
        }
        return $declaration;
    }

    /**
     * @param ClassReflection $classReflection
     * @return string
     */
    protected function getStartStatement(ClassReflection $classReflection)
    {
        $isInterface = $classReflection->isInterface();
        return sprintf(
            '%s%s%s%s',
            $classReflection->isAbstract() && !$isInterface ? 'abstract ' : '',
            $classReflection->isFinal() ? 'final ' : '',
            $isInterface ? 'interface ' : 'class ',
            $classReflection->getShortName()
        );
    }
}
