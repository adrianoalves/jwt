<?php

namespace AdrianoAlves\Jwt\Tests\Unit;

use AdrianoAlves\Jwt\Config;
use AdrianoAlves\Jwt\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class InstallPackageTest extends TestCase
{
    private string $configFile;

    public function setUp(): void
    {
        parent::setUp();

        $this->configFile = Config::CONFIG_FILE . ".php";
    }

    /** @test */
    function can_install_command_copies_the_configuration()
    {
        // make sure we're starting from a clean state
        if (File::exists(\config_path($this->configFile))) {
            \unlink(\config_path($this->configFile));
        }

        $this->assertFalse(File::exists(\config_path($this->configFile)));

        Artisan::call('jwt:install');

        $this->assertTrue(File::exists(\config_path($this->configFile)));
    }

    /** @test */
    public function can_users_overwrite_config_file()
    {
        // we already have an existing config file
        File::put(\config_path($this->configFile), 'test contents');
        $this->assertTrue(File::exists(\config_path($this->configFile)));

        // When we run the install command
        $command = $this->artisan('jwt:install');

        // We expect a warning that our configuration file exists
        $command->expectsConfirmation(
            'Config file already exists. Do you want to overwrite it?',
            // When answered with "yes"
            'yes'
        );

        // execute the command to force override
        $command->execute();

        $command->expectsOutput('Overwriting the configuration file...');

        // Assert that the original contents are overwritten
        $this->assertEquals(
            \file_get_contents(__DIR__.'/../../src/config/' . $this->configFile),
            \file_get_contents(\config_path($this->configFile))
        );

        // Clean up
        \unlink(\config_path($this->configFile));
    }
}
