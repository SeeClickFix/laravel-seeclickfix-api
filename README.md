# SeeClickFix wrapper for Laravel 4 - Alpha

Laravel Seeclickfix API is a simple laravel 4 service provider (wrapper) for [torann/seeclickfix-php-sdk]( https://github.com/torann/seeclickfix-php-sdk) 
witch provides API support in PHP 5.3+

---
 
- [Installation](#installation)
- [Registering the Package](#registering-the-package)
- [Configuration](#configuration)
- [Usage](#usage)
- [Basic usage](#basic-usage)

## Installation

Add laravel-seeclickfix-api to your composer.json file:

```
"require": {
  "torann/laravel-seeclickfix-api": "dev-master"
}
```

Use composer to install this package.

```
$ composer update
```

Create configuration file using artisan

```
$ php artisan config:publish torann/laravel-seeclickfix-api
```

## Configuration

### Registering the Package

Add an alias to the bottom of app/config/app.php

```php
'SeeClickFix' => 'SeeClickFix\API\Facade\API',
```

and register this service provider at the bottom of the `$providers` array:

```php
'SeeClickFix\API\APIServiceProvider',
```

### Credentials

Add your credentials to ``app/config/packages/torann/laravel-seeclickfix-api/config.php``

```php
return array( 
	
	/*
	|--------------------------------------------------------------------------
	| SeeClickFix Config
	|--------------------------------------------------------------------------
	*/

    'client_id'      => '',
    'client_secret'  => '',
    'redirect_uri'   => '', // Relative path

);
```

## Usage

### Basic usage

 - `getAuthorizationUri()`
 - `getAccessToken()`
 - `check()`
 - `getUserId()`
 - `logout()`

For a full list of API calls check the [torann/seeclickfix-php-sdk]( https://github.com/torann/seeclickfix-php-sdk/wiki) wiki. 

## Usage examples

Configuration:
Add your credentials to ``app/config/packages/torann/laravel-seeclickfix-api/config.php``

```php
    'client_id'     => 'Your client ID',
    'client_secret' => 'Your Client Secret',
    'redirect_uri'  => 'Your redirect URL',
),	
```
In your Controller use the following code:

```php
/**
 * Login user with SeeClickFix
 *
 * @return void
 */

public function loginWithSeeClickFix() {
	
	// get data from input
	$code = Input::get( 'code' );
	
	// check if code is valid
	if ( !empty( $code ) ) 
	{
		// Try to log the user in
        SeeClickFix::getAccessToken( $code );

		return Redirect::route("/")->with("success", "You have successfully logged in.");
	}
	// if not ask for permission first
	else {
		// get SeeClickFix authorization URL
		$url = SeeClickFix::getAuthorizationUri();
		
		// return to SeeClickFix login url
		return Response::make()->header( 'Location', (string)$url );
	}

}
```

In your Blade Views use the following code:

```php
@if (SeeClickFix::check())
	<li><a href="{{ route('logout') }}" class="external">Logout</a></li>
@else
	<li><a href="{{ route('login') }}" class="external">Login/Sign-up</a></li>
@endif
```