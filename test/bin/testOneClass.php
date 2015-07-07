<?php

require_once '../../vendor/autoload.php';

$cacher = new Cacher\Cacher();

$message = new \Zend\Stdlib\Message();

$classes = get_declared_classes();

$actual = $cacher->cache($classes);

$expected = file_get_contents('data/testOneClass_original.txt');

print PHP_EOL;

if ($actual === $expected) {
    print "OK";
} else print "FAIL";

print PHP_EOL;