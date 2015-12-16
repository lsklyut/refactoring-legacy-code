<?php

use Cacher\Cacher;

//require_once 'cache.php';
require_once 'vendor/autoload.php';

$cacher = new Cacher();

$result = $cacher->cache(['Zend\Stdlib\Message']);

print $result;