<?php

namespace Iankumu\Mpesa\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallMpesaPackage extends Command
{
    protected $signature = 'mpesa:install';

    protected $description = 'Install the Mpesa Package';

    public function handle()
    {
        $this->info('Installing Mpesa Package...');

        $this->info('Publishing configuration...');

        if (! $this->configExists('mpesa.php')) {
            $this->publishConfiguration();
            $this->info('Published configuration');
        } else {
            if ($this->shouldOverwriteConfig()) {
                $this->info('Overwriting configuration file...');
                $this->publishConfiguration(true);
            } else {
                $this->info('Existing configuration was not overwritten');
            }
        }

        $this->info('Installed Mpesa Package');
    }

    public function configExists($fileName)
    {
        return File::exists(config_path($fileName));
    }

    public function shouldOverwriteConfig()
    {
        return $this->confirm(
            'Config file already exists. Do you want to overwrite it?',
            false
        );
    }

    public function publishConfiguration($forcePublish = false)
    {
        $params = [
            '--provider' => "Iankumu\Mpesa\MpesaServiceProvider",
            '--tag' => 'mpesa-config',
        ];

        if ($forcePublish === true) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }
}
