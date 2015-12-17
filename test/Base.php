<?php

class Base extends PHPUnit_Framework_TestCase
{
    protected $reflectionCache = [];

    /**
     * Calls private or protected method
     *
     * @param string | object $object
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    protected function invokeRestrictedMethod($object, $method, $arguments = [])
    {
        $reflectionObject = $this->getReflection($object);
        $reflectionMethod = $reflectionObject->getMethod($method);
        $reflectionMethod->setAccessible(true);

        if (!empty($arguments)) {
            return $reflectionMethod->invokeArgs((is_string($object) ? null : $object), $arguments);
        }

        return $reflectionMethod->invoke((is_string($object) ? null : $object));
    }

    /**
     * Returns reflection object from instance or class name
     *
     * @param string | object $object
     * @return ReflectionClass|ReflectionObject
     * @throws InvalidArgumentException
     */
    protected function getReflection($object)
    {
        if (is_string($object) && class_exists($object)) {
            if (isset($this->reflectionCache[$object])) {
                return $this->reflectionCache[$object];
            }
            $reflection = new ReflectionClass($object);
            $this->reflectionCache[$object] = $reflection;
            return $reflection;
        } elseif (is_object($object)) {
            $objectHash = spl_object_hash($object);
            if (isset($this->reflectionCache[$objectHash])) {
                return $this->reflectionCache[$objectHash];
            }
            $reflection = new ReflectionObject($object);
            $this->reflectionCache[$objectHash] = $reflection;
            return $reflection;
        } else {
            throw new InvalidArgumentException('$object should be a valid class name or object instance');
        }
    }
}
