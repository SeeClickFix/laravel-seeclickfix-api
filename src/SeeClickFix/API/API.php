<?php namespace SeeClickFix\API;

use Illuminate\Support\ServiceProvider;
use Illuminate\Session\Store as SessionStore;

use SeeClickFixSDK;

class API {
    /**
     * The ID of the user that's been retrieved
     * and is used for authentication.
     *
     * @var String
     */
    protected $userId;

    /**
     * The user that's been retrieved and is used
     * for authentication. Authentication methods
     * are available for finding the user to set
     * here.
     *
     * @var \SeeClickFixSDK\User
     */
    protected $user;

    /**
     * The session store.
     *
     * @var \Illuminate\Session\Store
     */
    protected $session;

    /**
     * The config.
     *
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Current location key.
     *
     * @var String
     */
    protected $location = 'default';

    /**
     * The user provider, used for retrieving
     * objects which implement the SeeClickFix
     * API interface.
     *
     * @var \SeeClickFixSDK\SeeClickFix
     */
    protected $api;

    /**
     * OAuth token from SeeClickFix.
     *
     * @var String
     */
    protected $token;

    /**
     * Create a new API object.
     *
     * @param  string $client_id
     * @param  string $client_secret
     * @param  string $redirect_uri
     * @return void
     */
    public function __construct($config, SessionStore $session)
    {
        $this->location = $config["laravel-seeclickfix-api::location"];

        // Fallback to default
        if( ! $config["laravel-seeclickfix-api::{$this->location}"] ) {
            $this->location = 'default';
        }

        $this->api = new SeeClickFixSDK\SeeClickFix(array(
            'client_id'      => $config["laravel-seeclickfix-api::{$this->location}.client_id"],
            'client_secret'  => $config["laravel-seeclickfix-api::{$this->location}.client_secret"],
            'redirect_uri'   => \Request::root() . $config["laravel-seeclickfix-api::{$this->location}.redirect_uri"],
            'grant_type'     => $config["laravel-seeclickfix-api::grant_type"] ?: 'authorization_code',
            'sandbox'        => $config["laravel-seeclickfix-api::sandbox_mode"]
        ));

        $this->session = $session;

        $this->config = $config;

        // Get token from the session, if any
        $this->check();
    }

    /**
     * Get from value from config
     *
     * Returns the SeeClickFix config falue
     * @return mix
     * @access public
     */
    public function getConfig($key)
    {
        return $this->config["laravel-seeclickfix-api::{$this->location}.{$key}"];
    }

    /**
     * Authorize
     *
     * Returns the SeeClickFix authorization url
     * @return string Returns the access token URL
     * @access public
     */
    public function getAuthorizationUri()
    {
        return $this->api->getAuthorizationUri();
    }

    /**
     * Get the access token
     *
     * POSTs to the SeeClickFix API and obtains and access key
     *
     * @param string $code Code supplied by SeeClickFix
     * @return string Returns the access token
     * @throws \SeeClickFixSDK\Core\ApiException
     * @access public
     */
    public function getAccessToken( $code )
    {
        $token = $this->api->getAccessToken( $code );
        $this->api->setAccessToken($token);

        $this->user = $this->getCurrentUser();

        // Create an array of data to persist to the session
        $toPersist = array($this->user->getId(), $token);

        // Set sessions
        $this->session->put('seeclickfix_access_token', $toPersist);

        return $token;
    }

    /**
     * Check to see if the user is logged in
     *
     * @return bool
     */
    public function check()
    {
        // Check session first, follow by cookie
        if ( ! $userArray = $this->session->get('seeclickfix_access_token') )
        {
            return false;
        }

        // Now check our user is an array with two elements,
        // the username followed by the persist code
        if ( ! is_array($userArray) or count($userArray) !== 2)
        {
            return false;
        }

        list($this->userId, $token) = $userArray;

        $this->api->setAccessToken($token);

        return true;
    }

    /**
     * Returns the current user's ID, if any.
     *
     * @return String
     */
    public function getUserId()
    {
        // We will lazily attempt to load our user's ID
        if (is_null($this->userId))
        {
            $this->check();
        }

        return $this->userId;
    }

    /**
     * Get current user
     *
     * Returns the current user wrapped in a CurrentUser object
     *
     * @return \SeeClickFixSDK\CurrentUser
     * @access public
     */
    public function getCurrentUser()
    {
        // We will lazily attempt to load our user
        if (is_null($this->user))
        {
            $this->user = $this->api->getCurrentUser();
        }

        return $this->user;
    }

    /**
     * Logs the current user out.
     *
     * @return void
     */
    public function logout()
    {
        $this->user = null;
        $this->session->forget('seeclickfix_access_token');
    }

    /**
     * Proxy all methods to the api.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->api, $method), $args);
    }
}
