<?php

use Cacher\Cacher;

require_once '../vendor/autoload.php';

$cacher = new Cacher();

$message = new \Zend\Stdlib\Message();

$arrayObject = new \Zend\Stdlib\ArrayObject();

$filetIterator = new Zend\EventManager\Filter\FilterIterator();

$classes = get_declared_classes();

$result = $cacher->cache($classes);

$file = fopen('data/testMultipleClasses_original.txt', 'w');

fwrite($file, $result);
fclose($file);