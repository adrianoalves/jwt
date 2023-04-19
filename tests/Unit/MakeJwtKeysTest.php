<?php
namespace AdrianoAlves\Jwt\Tests\Unit;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;

use AdrianoAlves\Jwt\Config;
use AdrianoAlves\Jwt\Tests\TestCase;

class MakeJwtKeysTest extends TestCase
{
    private string $envFilePath;

    public function setUp(): void
    {
        parent::setUp();

        $this->envFilePath = App::environmentFilePath();
    }

    /** @test */
    function can_generate_command_generates_the_private_and_public_keys()
    {
        //generate the keys
        Artisan::call('make:jwt-keys');

        $envFileContent = file_get_contents($this->envFilePath);

        $privateKeyField = Config::ENV_PRIV_KEY_NAME;
        $publicKeyField  = Config::ENV_PUB_KEY_NAME;

        //assert that the secret key was created
        preg_match("#^ *{$privateKeyField} *= *[^\r\n]*$#uimU", $envFileContent, $matches);
        $this->assertNotNull($matches[0]);

        //assert that the public key was created
        preg_match("#^ *{$publicKeyField} *= *[^\r\n]*$#uimU", $envFileContent, $matches);
        $this->assertNotNull($matches[0]);
    }

    /** @test */
    function can_generate_command_overrides_current_private_and_public_keys()
    {
        //generate the keys
        Artisan::call('make:jwt-keys');

        $envFileContent = file_get_contents($this->envFilePath);

        $privateKeyField = Config::ENV_PRIV_KEY_NAME;
        $publicKeyField  = Config::ENV_PUB_KEY_NAME;

        //it holds on $matches the private key value...
        \preg_match("#^ *{$privateKeyField} *= *[^\r\n]*$#uimU", $envFileContent, $matches);
        $privateKeyOldPair = $matches[0];

        //...and it makes the same for the public key
        \preg_match("#^ *{$publicKeyField} *= *[^\r\n]*$#uimU", $envFileContent, $matches);
        $publicKeyOldPair = $matches[0];

        //generating the key again
        Artisan::call('make:jwt-keys');

        $envFileContent = \file_get_contents($this->envFilePath);

        //assert that old is not equal to new
        \preg_match("#^ *{$privateKeyField} *= *[^\r\n]*$#uimU", $envFileContent, $matches);
        $privateKeyNewPair = $matches[0];
        $this->assertNotEquals($privateKeyOldPair, $privateKeyNewPair);

        //get the old public key value pair
        \preg_match("#^ *{$publicKeyField} *= *[^\r\n]*$#uimU", $envFileContent, $matches);
        $publicKeyNewPair = $matches[0];
        $this->assertNotEquals($publicKeyOldPair, $publicKeyNewPair);
    }

}
