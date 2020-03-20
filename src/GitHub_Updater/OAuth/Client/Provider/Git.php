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

    private $githubClientID     = 'dcd540bdef714cc66b85';
    private $githubClientSecret = 'dda9b974507de454a41c84841a4512cdb6378f56';

    private $bitbucketClientID = 'VxtSLKyuDAe46eR54C';
    private $bitbucketClientSecret = 'ysSTGnn86Up2VAugH68hxEEmCWqn6dg9';

    private $gitlabClientID = 'de0cd8b159615595d573a14b95da80edfa3a3e3688557d0976489412ae1e77f6';
    private $gitlabClientSecret = '6568c2f607524fead4711b8a5c72c0b7c7de3abbd7ca18a32ef32414c7a7ab55';

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
	public $defaultScopes;

	/** Scope Separtor @var string */
	public $scopeSeparator;

	/** Self Installation of git server. @var bool */
	public $selfInstall;

	/**
	 * Constructor.
	 *
	 * Default is for GitHub.
	 */
	public function __construct( array $config ) {
		$this->domain         = isset( $config['domain'] ) ? $config['domain'] : $this->domain;
		$this->apiDomain      = isset( $config['apiDomain'] ) ? $config['apiDomain'] : $this->apiDomain;
		$this->urlAuthorize   = isset( $config['urlAuthorize'] ) ? $config['urlAuthorize'] : $this->urlAuthorize;
		$this->urlLogin       = isset( $config['urlLogin'] ) ? $config['urlLogin'] : $this->urlLogin;
		$this->apiUser        = isset( $config['apiUser'] ) ? $config['apiUser'] : $this->apiUser;
		$this->defaultScopes  = isset( $config['defaultScopes'] ) ? $config['defaultScopes'] : ['repo'];
		$this->scopeSeparator = isset( $config['scopeSeparator'] ) ? $config['scopeSeparator'] : ' ';
		$this->selfInstall    = isset( $config['selfInstall'] ) ? $config['selfInstall'] : false;
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
		return $this->defaultScopes;
	}

	/**
	 * Returns the string that should be used to separate scopes when building
	 * the URL for requesting an access token.
	 *
	 * @return string Scope separator, defaults to ','
	 */
	protected function getScopeSeparator() {
		return $this->scopeSeparator;
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
