<?php

namespace AdrianoAlves\Jwt\Commands;

use AdrianoAlves\Jwt\Commands\Concerns\OverwritesEnv;
use AdrianoAlves\Jwt\Config;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class JwtInstall extends Command
{
    use OverwritesEnv;

    protected $signature = 'jwt:install 
                                {--C|config=jwt : config file name, let it empty to use jwt as the file name. DO NOT SPECIFY THE .php Extension!}
                            ';

    protected $description = 'it installs and publish the config file if needed';

    public function handle()
    {
        $this->info('Bootstrapping JWT package...');

        $configFileName = $this->option('config');

        if (! $this->configExists($configFileName)) {
            $this->comment('Publishing configuration file...');
            $this->publishConfiguration();
            $this->info('Configuration file published successfully.');
        } else {
            if ($this->shouldOverwriteConfig()) {
                $this->info('Overwriting the configuration file...');
                $this->publishConfiguration(true);
            } else {
                $this->info('A configuration file already exists and was not overwritten');
            }
        }

        $this->writeEnvFile(Config::ENV_CONFIG_FILENAME, $configFileName);

        $this->info('Package AdrianoAlves/JWT installed on your project');
    }

    /**
     * @param string $fileName the config file name
     * @return bool true if the file exists, false otherwise
     */
    private function configExists(string $fileName = 'jwt'): bool
    {
        return File::exists(\config_path("$fileName.php"));
    }

    /**
     * @return bool
     */
    private function shouldOverwriteConfig(): bool
    {
        return $this->confirm(
            'There is a JWT config file. Do you want to overwrite it?',
            false
        );
    }

    private function publishConfiguration($forcePublish = false)
    {
        $params = [
            '--provider' => "AdrianoAlves\Jwt\JWTServiceProvider",
            '--tag' => 'config'
        ];

        if ($forcePublish === true) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }
}
