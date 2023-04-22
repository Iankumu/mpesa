<?php

namespace Iankumu\Mpesa\Tests\Unit;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

it('can copy config file', function () {

    // remove if exists
    if (File::exists(config_path('mpesa.php'))) {
        unlink(config_path('mpesa.php'));
    }

    expect(File::exists(config_path('mpesa.php')))->toBeFalse();

    Artisan::call('mpesa:install');

    expect(File::exists(config_path('mpesa.php')))->toBeTrue();
});
