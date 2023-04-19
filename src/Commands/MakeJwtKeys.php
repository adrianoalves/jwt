<?php

namespace AdrianoAlves\Jwt\Commands;

use AdrianoAlves\Jwt\Commands\Concerns\OverwritesEnv;
use AdrianoAlves\Jwt\Config;
use AdrianoAlves\Jwt\Exceptions\JwtKeyGenerationException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;

class MakeJwtKeys extends Command
{
    use OverwritesEnv;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:jwt-keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It makes the public-private key pair to create, sign and verify jwt tokens';

    public function __construct(
        protected string $environmentFilePath = ''
    )
    {
        parent::__construct();

        $this->environmentFilePath = $this->environmentFilePath ?? App::environmentFilePath();

        if (! File::exists($this->environmentFilePath)) {
            // when there is no .env, it creates an empty one
            File::put($this->environmentFilePath, "");
        }
    }
    /**
     * @return void
     */
    public function handle(): void
    {
        /** @todo replace it with priv/pub key generation using openssl */
        $keyPair = $this->getAsymmetricKeyPair();

        if ($this->writeEnvFile(Config::ENV_PRIV_KEY_NAME, $keyPair['private']) && $this->writeEnvFile(Config::ENV_PUB_KEY_NAME, $keyPair['public'])) {
            $this->info("JWT private and public keys successfully generated.");
        }
    }

    /**
     * @param ?array $options Optional key generation options, see https://www.php.net/manual/en/function.openssl-csr-new.php
     * @return array [ 'public', 'private' ]
     * @throws \Throwable
     */
    protected function getAsymmetricKeyPair(?array $options = null): array
    {
        $asymmetricKey = openssl_pkey_new([
            'digest_alg' => $options['algorithm'] ?? 'sha512-256',
            'private_key_bits' => $options['bits'] ?? 2048,
            'private_key_type' => $options['type'] ?? OPENSSL_KEYTYPE_DSA,
        ]);

        $exported = \openssl_pkey_export($asymmetricKey, $privateKey);
        $publicKey = \openssl_pkey_get_details($privateKey)['key'];

        \throw_unless($exported, new JwtKeyGenerationException('An Error occurred when generating public/private keys'));

        return [
            'private' => $privateKey,
            'public' => $publicKey
        ];
    }
}
