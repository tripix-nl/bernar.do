---
title: Using Laravel Passport JWT with multiple user providers
date: 2019-07-17
summary: Laravel Passport doesn't support having multiple user providers. By using custom claims in JWT we can pretty easily add support for that.
---

For a project we're currently working on I need an admin to be able to use the same API endpoints (consuming using JavaScript) as a user. So I created an `User` and `Admin` model, configured a general `api` guard as well as a `admin_api` guard, added middleware with both guards to the API endpoints. Pretty soon I discovered that Laravel Passport does not take into account the user provider for guard that is used for the API endpoints.

The topic of multi-auth is [widely](https://github.com/laravel/passport/issues/161) [discussed](https://github.com/laravel/passport/issues/982) on the Laravel Passport repository, but an example of the issue can be found [here](https://github.com/laravel/passport/blob/a738f62b72b9982c043c9d52e5d0438f999c630a/src/Token.php#L73).

```php
/**
 * Get the user that the token belongs to.
 *
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
public function user()
{
    $provider = config('auth.guards.api.provider');
    
    return $this->belongsTo(config('auth.providers.'.$provider.'.model'));
}
```

Which provider to use is fetched from the config, but with a hardcoded guard, namely `api`. In my case; I want to use two guards, the default named `api` with user provider `users` and the second guard `admin_api` with user provider `admins`. The way it works now is that it always resorts to the default `users` provider.

## JWT

Now I'm only interested in using my endpoints for JavaScript XHR requests, which uses JWT from Laravel Passport. To get this working I figured I could store the used model in the JWT and compare that when authenticating the request. Besides installing Laravel Passport and configuring it, I also followed the steps under [Consuming Your API With JavaScript](https://laravel.com/docs/5.8/passport#consuming-your-api-with-javascript) from the docs.

## Adding custom claims

First let's add a custom claim to the JWT to store which user model is used. We need to override the `createToken` method from the `ApiTokenCookieFactory` class to do so. Create a custom `ApiTokenCookieFactory` class.

```php
<?php

namespace App;

use Carbon\Carbon;
use Firebase\JWT\JWT;

class ApiTokenCookieFactory extends \Laravel\Passport\ApiTokenCookieFactory
{
    /**
     * Create a new JWT token for the given user ID and CSRF token.
     *
     * @param  mixed  $userId
     * @param  string  $csrfToken
     * @param  \Carbon\Carbon  $expiration
     * @return string
     */
    protected function createToken($userId, $csrfToken, Carbon $expiration)
    {
        return JWT::encode([
            'sub' => $userId,
            'csrf' => $csrfToken,
            'expiry' => $expiration->getTimestamp(),
            // Added custom claim to store used user model in the JWT
            'model' => get_class(request()->user()),
        ], $this->encrypter->getKey());
    }
}
```

To tell Laravel Passport it needs to use our custom `ApiTokenCookieFactory`, we need to add a custom service provider, that extends the default Laravel Passport service provider. Don't forget to add the custom service provider to the list of providers in `app/config.php`.

```php
<?php

namespace App\Providers;

use Laravel\Passport\ApiTokenCookieFactory;

class PassportServiceProvider extends \Laravel\Passport\PassportServiceProvider
{
    public function register()
    {
        parent::register();

        $this->app->bind(ApiTokenCookieFactory::class, \App\ApiTokenCookieFactory::class);
    }
}
```

## Fixing the Token Guard

We need to make changes to the TokenGuard that Laravel Passport uses. We compare the user model for the current guard's user provider, to the user model stored in the JWT. Since we only need this to work for JWT (and not OAuth etc.) we can override the `authenticateViaCookie` method in the TokenGuard class. 

```php
<?php

namespace App;

use Laravel\Passport\TransientToken;

class TokenGuard extends \Laravel\Passport\Guards\TokenGuard
{
    /**
     * Authenticate the incoming request via the token cookie.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function authenticateViaCookie($request)
    {
        if (! $token = $this->getTokenViaCookie($request)) {
            return;
        }

        // Compare model stored in JWT with provider model
        if ($this->provider->getModel() !== $token['model']) {
            return;
        }

        // If this user exists, we will return this user and attach a "transient" token to
        // the user model. The transient token assumes it has all scopes since the user
        // is physically logged into the application via the application's interface.
        if ($user = $this->provider->retrieveById($token['sub'])) {
            return $user->withAccessToken(new TransientToken);
        }
    }
}
```

Let's expand our custom `PassportServiceProvider` to tell Laravel Passport to use our custom `TokenGuard`.

```php
<?php

namespace App\Providers;

use App\TokenGuard;
use Illuminate\Auth\RequestGuard;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\ApiTokenCookieFactory;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\ResourceServer;

class PassportServiceProvider extends \Laravel\Passport\PassportServiceProvider
{
    public function register()
    {
        parent::register();

        $this->app->bind(ApiTokenCookieFactory::class, \App\ApiTokenCookieFactory::class);
    }

    /**
     * Make an instance of the token guard.
     *
     * @param  array  $config
     * @return \Illuminate\Auth\RequestGuard
     */
    protected function makeGuard(array $config)
    {
        return new RequestGuard(function ($request) use ($config) {
            return (new TokenGuard(
                $this->app->make(ResourceServer::class),
                Auth::createUserProvider($config['provider']),
                $this->app->make(TokenRepository::class),
                $this->app->make(ClientRepository::class),
                $this->app->make('encrypter')
            ))->user($request);
        }, $this->app['request']);
    }
}
```

That should be it! Summarising the changes;
- create a `PassportServiceProvider.php` in `app/Providers`.
- create a `ApiTokenCookieFactory` in `app`.
- create a `TokenGuard` in `app`.
- modify the providers array in `config/app.php` to include `\App\Providers\PassportServiceProvider::class`.

## In closing

There is probably a lot to improve here, let me know! For example; how I'm getting the request user model in the `createToken` method might not be the best idea. But it's a solution that might come in handy. We might replace Laravel Passport with something custom made, since we're only using it for consuming our own API with JavaScript.
