<?php

use Cacher\Cacher;

require_once 'vendor/autoload.php';

$cacher = new Cacher();

// Use a class so that it is autoloaded
$message = new \Zend\Stdlib\Message();

$classes = get_declared_classes();

$result = $cacher->cache($classes);

print $result;