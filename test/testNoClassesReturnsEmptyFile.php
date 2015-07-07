<?php

use Cacher\Cacher;

require_once '../vendor/autoload.php';

$cacher = new Cacher();

$classes = get_declared_classes();

$result = $cacher->cache($classes);

$file = fopen('data/testNoClassesReturnsEmptyFile_original.txt', 'w');

fwrite($file, $result);
fclose($file);