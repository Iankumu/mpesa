<?php

namespace Iankumu\Mpesa\Tests;

use Iankumu\Mpesa\Mpesa;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * Set up
     */
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'https://49cb48b01f608f.lhr.life']);
    }

    /*
     * Define environment setup.
     *
     * @param  Application $app
     * @return void
    */

    protected function getEnvironmentSetUp($app)
    {
        // Alter the testing mpesa environment
        $app['config']->set('mpesa.environment', 'sandbox');
    }

    /**
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['Iankumu\Mpesa\MpesaServiceProvider'];
    }

    /** @test */
    public function false()
    {
        $this->assertFalse(false);
    }
}
