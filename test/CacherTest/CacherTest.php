<?php

namespace CacherTest;

use Cacher\Cacher;

class CacherTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCache()
    {
        $this->assertNotNull(new Cacher());
    }
}