<?php namespace SeeClickFix\API;

/**
 * Auth class
 *
 * Handles authentication
 */
class Auth
{
    /**
     * The session store used by the guard.
     *
     * @var \Illuminate\Session\Store
     */
    protected $session;

    /**
     * The user provider, used for retrieving
     * objects which implement the SeeClickFix
     * API interface.
     *
     * @var \SeeClickFixSDK\SeeClickFix
     */
    protected $api;

    /**
     * Create a new Auth object.
     *
     * @param  string $client_id
     * @param  string $client_secret
     * @param  string $redirect_uri
     * @return void
     */
    public function __construct($client_id, $client_secret, $redirect_uri = null, SessionStore $session)
    {
        $this->api = new SeeClickFixSDK\SeeClickFix(array(
            'client_id'      => $client_id,
            'client_secret'  => $client_secret,
            'redirect_uri'   => $redirect_uri
        ));

        $this->session = $session;
    }

    /**
     * Authorize
     *
     * Returns the SeeClickFix authorization url
     * @return string Returns the access token URL
     * @access public
     */
    public function getAuthorizationUri() {
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
    public function getAccessToken( $code ) {

        $token = $this->api->getAccessToken( $code );
        $this->session->put('seeclickfix_access_token', $token);

        return $token;
    }

}