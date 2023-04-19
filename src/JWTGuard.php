<?php

namespace AdrianoAlves\Jwt;

use Symfony\Component\HttpFoundation\Request;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class JWTGuard implements Guard
{
    use GuardHelpers;

    /**
     * authenticatable user.
     *
     * @var Authenticatable|null
     */
    protected ?Authenticatable $user;

    /**
     * Request
     *
     * @var Request
     */
    private Request $request;

    /**
     * Jwt implementation class of the selected jwt library
     *
     * @var TokenFactory
     */
    private TokenFactory $tokenFactory;

    /**
     * Constructor
     *
     * @param UserProvider $provider
     * @param Request $request
     */
    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;

        $this->request = $request;

        $this->tokenFactory = new TokenFactory();
    }

    /**
     * Get the user by validating the token from the request
     *
     * @return Authenticatable|null
     */
    public function user(): Authenticatable|null
    {
        if ($this->user === null) {
            $user = null;

            $token = $this->request->bearerToken();

            if (! empty($token)) {
                $user = $this->getUserToken($token);
            }

            return $this->user = $user;
        }

        return $this->user;
    }

    /**
     * Get the user of the token
     *
     * @param string $token
     * @return Authenticatable|null
     */
    public function getUserToken(string $token): Authenticatable|null
    {
        //get the jwt token
        $userId = $this->tokenFactory->validate($token);

        return $userId ? $this->provider->retrieveById($userId) : null;
    }

    /**
     * Validate supplied credentials
     *
     * @param array $credentials
     * @return bool
     */
    public function validate(array $credentials = []): bool
    {
        $user = $this->provider->retrieveByCredentials($credentials);
        return $user !== null;
    }

    /**
     * Validates user's credentials and returns access token
     *
     * @param array $credentials
     * @return string|null
     */
    public function attempt(array $credentials = []): ?string
    {
        $token = null;
        $user = $this->provider->retrieveByCredentials($credentials);

        if ($user !== null && $this->provider->validateCredentials($user, $credentials)) {
            // forging a new access token
            $identityField = config(\env(Config::ENV_CONFIG_FILENAME) . '.user_identifier');
            $token = $this->tokenFactory->issueToken($user->$identityField);
        }

        return $token;
    }
}
