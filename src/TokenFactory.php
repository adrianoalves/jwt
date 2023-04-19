<?php

namespace AdrianoAlves\Jwt;

use Exception;
use Lcobucci\JWT\Signer;
use Illuminate\Support\Str;
use Lcobucci\JWT\Configuration;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;

class TokenFactory
{
    /**
     * @var Configuration
     */
    private Configuration $config;

    /**
     * JWT issued by
     *
     * @var string
     */
    protected string $issuer;

    /**
     * JWT Expiry in seconds
     *
     * @var int
     */
    protected int $ttl;

    /**
     * JWT private key
     *
     * @var string
     */
    protected string $privateKey;

    /**
     * JWT public key
     *
     * @var string
     */
    protected string $publicKey;

    /**
     * Constructor
     */
    public function __construct()
    {
        $config = \config(\env(Config::ENV_CONFIG_FILENAME));

        $this->privateKey = $config['private_key'];
        $this->publicKey  = $config['public_key'];
        $this->issuer     = $config['issuer'];
        $this->ttl        = $config['ttl'];

        $this->bootConfig();
    }

    /**
     * Issue the jwt and return the token as a string
     *
     * @param string $userIdentifier
     * @return string
     */
    public function issueToken(string $userIdentifier): string
    {
        $now = new \DateTimeImmutable();
        $uniqueId = Str::random(16);
        $expiresAt = $now->modify('+ '. $this->ttl . ' seconds');

        $token = $this->config->builder()
            ->issuedBy($this->issuer) // Configures the issuer (iss claim)
            ->identifiedBy($uniqueId) // Configures the id (jti claim), replicating as a header item
            ->issuedAt($now) // Configures the time that the token was issued (iat claim)
            ->canOnlyBeUsedAfter($now) // Configures when the token can be used (nbf claim)
            ->expiresAt($expiresAt) // Configures the expiration time of the token (exp claim)
            ->withClaim('uid', $userIdentifier) // Configures a new claim, called "uid"
            ->getToken($this->config->signer(), $this->config->signingKey()); // Builds a new token

        return new $token->toString();
    }

    /**
     * Get the stored jwtToken when token is validated
     *
     * @param string $token
     * @return mixed
     */
    public function validate(string $token): mixed
    {
        //parse the token
        /** @var UnencryptedToken $unencryptedToken */
        $unencryptedToken = $this->config->parser()->parse($token);

        //validate token against the constraints set
        $constraints = $this->config->validationConstraints();
        if ($this->config->validator()->validate($unencryptedToken, ...$constraints)) {
            //get the user identifier
            return $unencryptedToken->claims()->get('uid');
        }

        return null;
    }

    /**
     * Boots the token factory configuration
     */
    protected function bootConfig(): void
    {
        $signer = new Signer\Hmac\Sha512();
        $privateKey = InMemory::plainText($this->privateKey);
        $publicKey  = InMemory::plainText($this->publicKey);

        //set config for asymmetric signer
        $this->config = Configuration::forAsymmetricSigner($signer, $privateKey, $publicKey);

        $clock = SystemClock::fromSystemTimezone();

        $this->config->setValidationConstraints(
            new IssuedBy($this->issuer),
            new SignedWith($signer, $privateKey),
            new StrictValidAt($clock),
            new LooseValidAt($clock)
        );
    }
}
