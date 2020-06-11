<?php

namespace Thecodebunny\Bapubuilder\Tests;

use Orchestra\Testbench\TestCase;
use Thecodebunny\Bapubuilder\BapubuilderServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [BapubuilderServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
