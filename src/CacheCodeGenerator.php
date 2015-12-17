<?php

namespace Cacher;

use Zend\Code\Reflection\ClassReflection;

class CacheCodeGenerator
{
    /** @var Factory */
    protected $factory;

    public function __construct(Factory $factory = null)
    {
        $this->factory = $factory ?: new Factory();
    }

    /**
     * @param ClassReflection $classReflection
     * @return string
     */
    public function getCacheCode(ClassReflection $classReflection)
    {
        $useString = $this->buildUseString($classReflection);
        $usesNames = $this->buildUseNames($classReflection);
        $declaration = $this->buildDeclareStatement($classReflection, $usesNames);
        $directory = dirname($classReflection->getFileName());
        $contents = trim(str_replace(__DIR__, sprintf("'%s'", $directory), $classReflection->getContents(false)));

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
        /** @var ClassReflection | bool */
        $parent = $classReflection->getParentClass();
        /** @var string */
        $namespace = $classReflection->getNamespaceName();
        return $this->buildStartStatement($classReflection)
            . $this->buildExtendsStatement($parent, $namespace)
            . $this->buildInterfaceStatement($classReflection, $parent, $namespace, $usesNames);
    }

    /**
     * Partially build the start statement for the class or interface declaration.
     *
     * @param ClassReflection $classReflection
     * @return string
     */
    protected function buildStartStatement(ClassReflection $classReflection)
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

    /**
     * Build the extends statement for the class declaration statement.
     *
     * @param ClassReflection | bool $parent
     * @param string $namespace
     * @return string
     */
    protected function buildExtendsStatement($parent, $namespace)
    {
        $extendsStatement = null;
        $name = $parent ? $parent->getName() : '';
        if ($parent && $namespace) {
            $extendsStatement = array_key_exists($name, $usesNames)
                ? ($usesNames[$name] ?: $parent->getShortName())
                : ((0 === strpos($name, $namespace))
                    ? substr($name, strlen($namespace) + 1)
                    : '\\' . $name);
        } elseif ($parent && !$namespace) {
            $extendsStatement = '\\' . $name;
        }
        return $extendsStatement ? " extends {$extendsStatement}" : '';
    }

    /**
     * Build interface extends or implement statements.
     *
     * @param ClassReflection $classReflection
     * @param ClassReflection | bool $parent
     * @param string $namespace
     * @param array $usesNames
     * @return string
     */
    protected function buildInterfaceStatement(ClassReflection $classReflection, $parent, $namespace, array $usesNames)
    {
        $interfaces = array_diff($classReflection->getInterfaceNames(), $parent ? $parent->getInterfaceNames() : []);
        if (!count($interfaces)) {
            return '';
        }
        $iCollection = $this->extractInterfaces($interfaces);
        return sprintf('%s%s',
            $classReflection->isInterface() ? ' extends ' : ' implements ',
            implode(', ', $this->getInterfaceNames($classReflection, $usesNames, $iCollection, $namespace))
        );
    }

    /**
     * Extract all related interfaces.
     *
     * @param array
     * @return array
     */
    protected function extractInterfaces(array $interfaces)
    {
        foreach ($interfaces as $interface) {
            $iReflection = $this->factory->getNewClassReflection($interface);
            $interfaces = array_diff($interfaces, $iReflection->getInterfaceNames());
        }
        return $interfaces;
    }

    /**
     * Get an array of all interfaces names.
     *
     * @param ClassReflection $classReflection
     * @param array $usesNames
     * @param array $iCollection
     * @param string $namespace
     * @return array
     */
    protected function getInterfaceNames(ClassReflection $classReflection, array $usesNames, array $iCollection, $namespace)
    {
        $factory = $this->factory;
        return array_map(function ($interface) use ($usesNames, $classReflection, $namespace, $factory) {
            $iReflection = $factory->getNewClassReflection($interface);
            $name = $iReflection->getName();
            return (array_key_exists($name, $usesNames)
                ? ($usesNames[$name] ?: $iReflection->getShortName())
                : ((0 === strpos($name, $namespace))
                    ? substr($name, strlen($namespace) + 1)
                    : '\\' . $name));
        }, $iCollection);
    }
}
