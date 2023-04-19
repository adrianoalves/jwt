# AdrianoAlves/Jwt

A JSON Web Token Library made to adapt the **[lcobucci/jwt](https://github.com/lcobucci/jwt)** for Laravel and Lumen. 

It uses Asymmetric Algorithm using a **private key** for signature creation and a **public key** for verification. This means that it's fine to distribute your **public key**. However, the **private key** should **remain secret**.

## Laravel Installation

Via composer

    composer require adrianoalves/jwt

Install the package

    php artisan jwt:install

Generate private and public keys

    php artisan make:jwt-keys

Modify the jwt.php in your config file as necessary and add your app's Policies if necessary.

Change the route driver in your auth.php config file to jwt.

    'guards' => [
        'jwt' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],
    ],

## Auth Guard Usage

### Routing
    Route::middleware('auth:jwt')->get('/user', function (Request $request) {
        return $request->user();
    });

    // if you set jwt as driver for your api guard
    Route::middleware('auth:api')->get('/user', function (Request $request) {
        return $request->user();
    });

### Login

    // Generate a token for the user if the credentials are valid
    $token = Auth::attempt($credentials);

### User
    
    // Getting the currently authenticated user
    $user = Auth::user();
