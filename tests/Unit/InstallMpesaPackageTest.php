<?php

namespace Iankumu\Mpesa\Tests\Unit;

use Iankumu\Mpesa\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class InstallMpesaPackageTest extends TestCase
{
    /** @test */
    public function copy_config_file()
    {
        // remove if exists
        if (File::exists(config_path('mpesa.php'))) {
            unlink(config_path('mpesa.php'));
        }

        $this->assertFalse(File::exists(config_path('mpesa.php')));

        Artisan::call('mpesa:install');

        $this->assertTrue(File::exists(config_path('mpesa.php')));
    }
}
