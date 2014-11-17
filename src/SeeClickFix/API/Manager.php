<?php namespace SeeClickFix\API;

use Illuminate\Session\Store as SessionStore;

use SeeClickFix\SeeClickFix;

class Manager
{
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
     * @var \SeeClickFix\User
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
     * The user provider, used for retrieving
     * objects which implement the SeeClickFix
     * API interface.
     *
     * @var \SeeClickFix\SeeClickFix
     */
    protected $api;

    /**
     * OAuth token from SeeClickFix.
     *
     * @var String
     */
    protected $token;

    /**
     * Create a new Manager object.
     *
     * @param  array  $config
     * @param  \Illuminate\Session\Store  $session
     */
    public function __construct($config, SessionStore $session)
    {
        $this->api = new SeeClickFix(array(
            'client_id'      => $config["laravel-seeclickfix-api::client_id"],
            'client_secret'  => $config["laravel-seeclickfix-api::client_secret"],
            'redirect_uri'   => \Request::root() . $config["laravel-seeclickfix-api::redirect_uri"],
            'sandbox'        => $config["laravel-seeclickfix-api::sandbox_mode"]
        ));

        $this->session = $session;

        $this->config = $config;

        // Get token from the session, if any
        $this->check();
    }

    /**
     * Authorize
     *
     * Returns the SeeClickFix authorization url
     *
     * @return string Returns the access token URL
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
     * @throws \SeeClickFix\Core\ApiException
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
     * @return \SeeClickFix\CurrentUser
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
