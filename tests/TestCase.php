<?php

namespace Iankumu\Mpesa\Tests;

use Iankumu\Mpesa\MpesaServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * Set up
     */
    protected function setUp(): void
    {
        parent::setUp();
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
        $app['config']->set('mpesa.mpesa_consumer_key', '12345');
        $app['config']->set('mpesa.mpesa_consumer_secret', '12345');
        $app['config']->set('mpesa.callback_url', null);
    }

    /**
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            MpesaServiceProvider::class
        ];
    }

    /** @test */
    public function false()
    {
        $this->assertFalse(false);
    }
}
