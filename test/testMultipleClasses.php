<?php

use Cacher\Cacher;

require_once '../vendor/autoload.php';

$cacher = new Cacher();

$message = new \Zend\Stdlib\Message();

$arrayObject = new \Zend\Stdlib\ArrayObject();

$filetIterator = new Zend\EventManager\Filter\FilterIterator();

$classes = get_declared_classes();

$actual = $cacher->cache($classes);

$expected = file_get_contents('data/testMultipleClasses_original.txt');

print PHP_EOL;

if ($actual === $expected) {
    print "OK";
} else print "FAIL";

print PHP_EOL;