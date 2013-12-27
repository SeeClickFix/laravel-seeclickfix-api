# SeeClickFix wrapper for Laravel 4 - Alpha

[![Latest Stable Version](https://poser.pugx.org/seeclickfix/laravel-seeclickfix-api/v/stable.png)](https://packagist.org/packages/seeclickfix/laravel-seeclickfix-api) [![Total Downloads](https://poser.pugx.org/seeclickfix/laravel-seeclickfix-api/downloads.png)](https://packagist.org/packages/seeclickfix/laravel-seeclickfix-api)

Laravel Seeclickfix API is a simple laravel 4 service provider (wrapper) for [seeclickfix/seeclickfix-php-sdk]( https://github.com/seeclickfix/seeclickfix-php-sdk) 
which provides API support in PHP 5.3+

---
 
- [Installation](#installation)
- [Registering the Package](#registering-the-package)
- [Configuration](#configuration)
- [Usage](#usage)
- [Basic usage](#basic-usage)
- [License](#license)

## Installation

Add laravel-seeclickfix-api to your composer.json file:

~~~
"require": {
  "seeclickfix/laravel-seeclickfix-api": "dev-master"
}
~~~

Use composer to install this package.

~~~
$ composer update
~~~

Create configuration file using artisan

~~~
$ php artisan config:publish seeclickfix/laravel-seeclickfix-api
~~~

## Configuration

### Registering the Package

Add an alias to the bottom of app/config/app.php

~~~php
'SeeClickFix' => 'SeeClickFix\API\Facade\API',
~~~

and register this service provider at the bottom of the `$providers` array:

~~~php
'SeeClickFix\API\APIServiceProvider',
~~~

### Credentials

Add your credentials to ``app/config/packages/seeclickfix/laravel-seeclickfix-api/config.php``

~~~php
return array( 
	
	/*
	 |--------------------------------------------------------------------------
	 | Settings
	 |--------------------------------------------------------------------------
	 */

    'location'	     => 'default',
    'sandbox_mode'	 => false,

	/*
	 |--------------------------------------------------------------------------
	 | Locations
	 |--------------------------------------------------------------------------
	 */

	'default'    => array(

	    'client_id'      => '',
	    'client_secret'  => '',
	    'redirect_uri'   => '', // Relative path
	    "lat" => 41.29841599999985,
	    "lng" => -72.9291785

	),

);
~~~

When developing your application set `sandbox_mode` to _true_. This will allow you to test out features on our test server.

`location` is used with apps that support multiple locations.

## Usage

### Basic usage

**SeeClickFix::getAuthorizationUri()** -This will redirect the user to the SeeClickFix authorization page.

**SeeClickFix::getAccessToken()** - Gets access token and validates it.

**SeeClickFix::check()** - Determine if the user is logged in.

**SeeClickFix::getUserId( $id )** - Returns a single user by `id`.

**SeeClickFix::logout()** - Log current user out.

For a full list of API calls check the [seeclickfix/seeclickfix-php-sdk]( https://github.com/seeclickfix/seeclickfix-php-sdk) wiki. 

## Usage examples

In your Controller use the following code:

~~~php
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
~~~

In your Blade Views use the following code:

~~~php
@if (SeeClickFix::check())
	<li><a href="{{ route('logout') }}" class="external">Logout</a></li>
@else
	<li><a href="{{ route('login') }}" class="external">Login/Sign-up</a></li>
@endif
~~~

##License

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with the License. You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.

**Copyright 2013 SeeClickFix**
