<?php

namespace Fragen\GitHub_Updater\OAuth\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

// Maybe just use GenericProvider.
// use League\OAuth2\Client\Provider\GenericProvider;
// use \League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class Git extends AbstractProvider {
	use BearerAuthorizationTrait;

	/** Domain.  @var string */
	public $domain = 'https://github.com';

	/** API domain. @var string */
	public $apiDomain = 'https://api.github.com';

	/** API route. @var string */
	public $apiRoute = '/api/v3';

	/** Authorize URL. @var string */
	public $urlAuthorize = '/login/oauth/authorize';

	/** Login URL. @var string */
	public $urlLogin = '/login/oauth/access_token';

	/** Token Scopes. @var array */
	public $scopes;

	/** Self Installation of git server. @var bool */
	public $selfInstall;

    /**
     * Constructor.
     */
	public function __construct( array $config ) {
		$this->domain       = isset( $config['domain'] ) ? $config['domain'] : $this->domain;
		$this->apiDomain    = isset( $config['apiDomain'] ) ? $config['apiDomain'] : $this->apiDomain;
		$this->urlAuthorize = isset( $config['urlAuthorize'] ) ? $config['urlAuthorize'] : $this->urlAuthorize;
		$this->urlLogin     = isset( $config['urlLogin'] ) ? $config['urlLogin'] : $this->urlLogin;
		$this->apiUser      = isset( $config['apiUser'] ) ? $config['apiUser'] : $this->apiUser;
		$this->scopes       = isset( $config['scopes'] ) ? $config['scopes'] : [];
		$this->selfInstall  = isset( $config['selfInstall'] ) ? $config['selfInstall'] : false;
	}

	/**
	 * Get authorization url to begin OAuth flow
	 *
	 * @return string
	 */
	public function getBaseAuthorizationUrl() {
		return $this->domain . $this->urlAuthorize;
	}

	/**
	 * Get access token url to retrieve token
	 *
	 * @param array $params
	 *
	 * @return string
	 */
	public function getBaseAccessTokenUrl( array $params ) {
		return $this->domain . $this->urlLogin;
	}

	/**
	 * Get provider url to fetch user details
	 *
	 * @param AccessToken $token
	 *
	 * @return string
	 */
	public function getResourceOwnerDetailsUrl( AccessToken $token ) {
		$baseApiRoute = $this->selfInstall ? $this->domain . $this->apiRoute : $this->apiDomain;

		return $baseApiRoute . '/user';
	}

	/**
	 * Get the default scopes used by this provider.
	 *
	 * This should not be a complete list of all scopes, but the minimum
	 * required for the provider user interface!
	 *
	 * @return array
	 */
	protected function getDefaultScopes() {
		return $this->scopes;
	}

	/**
	 * Check a provider response for errors.
	 *
	 * @link   https://developer.github.com/v3/#client-errors
	 * @link   https://developer.github.com/v3/oauth/#common-errors-for-the-access-token-request
	 * @throws IdentityProviderException
	 * @param  ResponseInterface $response
	 * @param  array             $data     Parsed response data
	 * @return void
	 */
	protected function checkResponse( ResponseInterface $response, $data ) {
		if ( $response->getStatusCode() >= 400 ) {
			throw GitIdentityProviderException::clientException( $response, $data );
		} elseif ( isset( $data['error'] ) ) {
			throw GitIdentityProviderException::oauthException( $response, $data );
		}
	}

	/**
	 * Generate a user object from a successful user details request.
	 *
	 * @param  array       $response
	 * @param  AccessToken $token
	 * @return \League\OAuth2\Client\Provider\ResourceOwnerInterface
	 */
	protected function createResourceOwner( array $response, AccessToken $token ) {
		$user = new GitResourceOwner( $response );

		return $user->setDomain( $this->domain );
	}
}
