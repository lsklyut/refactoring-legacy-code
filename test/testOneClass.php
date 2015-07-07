<?php

use Cacher\Cacher;

require_once '../vendor/autoload.php';

$cacher = new Cacher();

$message = new \Zend\Stdlib\Message();

$classes = get_declared_classes();

$result = $cacher->cache($classes);

$file = fopen('data/testOneClass_original.txt', 'w');

fwrite($file, $result);
fclose($file);