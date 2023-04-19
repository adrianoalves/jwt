<?php

return [
    /**
     * User id field to relate a jwt token to a user
     */
    "user_identifier" => "id",

    /**
     * Private key for signing jwt token
     */
    "private_key" => \env('JWT_PRIV_KEY') ,

    /**
     * Public key for signing jwt token
     */
    "public_key" => \env('JWT_PUB_KEY'),

    /**
     * time-to-live of a token in seconds
     */
    "ttl" => \intval( \env("JWT_TTL", 12 * 60 * 60) ),

    /**
     * Token Issuer
     */
    "issuer" => \strval( \env("APP_URL", "http://localhost") ),
];
