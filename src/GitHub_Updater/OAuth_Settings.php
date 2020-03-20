<?php
/**
 * GitHub Updater
 *
 * @author    Andy Fragen
 * @license   GPL-2.0+
 * @link      https://github.com/afragen/github-updater
 * @package   github-updater
 */

namespace Fragen\GitHub_Updater;

use Fragen\GitHub_Updater\Traits\GHU_Trait;
use Fragen\GitHub_Updater\OAuth\Client\Provider\Git;

/**
 * Class Remote_Management
 */
class OAuth_Settings {
	use GHU_Trait;

	/**
	 * Remote_Management constructor.
	 */
	public function __construct() {
	}

	public function run() {
		$this->load_hooks();
	}

	/**
	 * Load needed action/filter hooks.
	 */
	public function load_hooks() {
		add_action( 'admin_init', [ $this, 'oauth_page_init' ] );
		// add_action(
		// 'github_updater_update_settings',
		// function ( $post_data ) {
		// $this->save_settings( $post_data );
		// }
		// );
		$this->add_settings_tabs();
	}

	/**
	 * Save Remote Management settings.
	 *
	 * @uses 'github_updater_update_settings' action hook
	 * @uses 'github_updater_save_redirect' filter hook
	 *
	 * @param array $post_data $_POST data.
	 */
	public function save_settings( $post_data ) {
		if ( isset( $post_data['option_page'] ) &&
		'github_updater_oauth_settings' === $post_data['option_page']
		) {
			$options = isset( $post_data['github_updater_oauth_settings'] )
			? $post_data['github_updater_oauth_settings']
			: [];

			update_site_option( 'github_updater_oauth_settings', (array) $this->sanitize( $options ) );

			add_filter(
				'github_updater_save_redirect',
				function ( $option_page ) {
					return array_merge( $option_page, [ 'github_updater_oauth_settings' ] );
				}
			);
		}
	}

	/**
	 * Adds Remote Management tab to Settings page.
	 */
	public function add_settings_tabs() {
		$install_tabs = [ 'github_updater_oauth_settings' => esc_html__( 'OAuth Settings', 'github-updater' ) ];
		add_filter(
			'github_updater_add_settings_tabs',
			function ( $tabs ) use ( $install_tabs ) {
				return array_merge( $tabs, $install_tabs );
			},
			5
		);
		add_filter(
			'github_updater_add_admin_page',
			function ( $tab, $action ) {
				$this->add_admin_page( $tab, $action );
			},
			10,
			2
		);
	}

	/**
	 * Add Settings page data via action hook.
	 *
	 * @uses 'github_updater_add_admin_page' action hook
	 *
	 * @param string $tab    Tab name.
	 * @param string $action Form action.
	 */
	public function add_admin_page( $tab, $action ) {
		if ( 'github_updater_oauth_settings' === $tab ) {
			$action = add_query_arg( 'tab', $tab, $action );
			?>
			<form class="settings" method="post" action="<?php esc_attr_e( $action ); ?>">
			<?php
			// phpcs:ignore Squiz.Commenting.InlineComment.InvalidEndChar
			// settings_fields( 'github_updater_remote_management' );
			do_settings_sections( 'github_updater_oauth_settings' );
			// phpcs:ignore Squiz.Commenting.InlineComment.InvalidEndChar
			// submit_button();

			echo '</form>';
		}
	}

	/**
	 * Settings for Remote Management.
	 */
	public function oauth_page_init() {
		register_setting(
			'github_updater_oauth_settings',
			'github_updater_oauth_settings',
			[ $this, 'sanitize' ]
		);

		add_settings_section(
			'oauth_settings',
			esc_html__( 'OAuth Settings', 'github-updater' ),
			[ $this, 'print_section_oauth_settings' ],
			'github_updater_oauth_settings'
		);
	}

	/**
	 * Print the OAuth text.
	 */
	public function print_section_oauth_settings() {

		echo '<p>';
		esc_html_e( 'GitHub has recently deprecated the use of access tokens with their API. This is causing users to be inundated with emails describing this issue. I am aware and working on a solution.', 'github-updater' );
		echo '</p>';

		echo '<p>';
		printf(
			wp_kses_post(
				/* translators: %s: Link to Git Remote Updater repository */
				__( 'You can help over on <a href="%s">issue 848</a>.', 'github-updater' )
			),
			'https://github.com/afragen/github-updater/issues/848'
		);
		echo '</p>';

		echo '<p>Eventually buttons to get an OAuth token will be here.</p>';
		echo '<a class="button primary-button disabled">Get OAuth Token</a>';
	}

	/**
	 * Get the settings option array and print one of its values.
	 * For remote management settings.
	 *
	 * @param array $args Checkbox args.
	 *
	 * @return bool|void
	 */
	public function token_callback_checkbox_remote( $args ) {
		$checked = isset( self::$options_remote[ $args['id'] ] ) ? self::$options_remote[ $args['id'] ] : null;
		?>
		<label for="<?php esc_attr_e( $args['id'] ); ?>">
			<input type="checkbox" id="<?php esc_attr_e( $args['id'] ); ?>" name="github_updater_remote_management[<?php esc_attr_e( $args['id'] ); ?>]" value="1" <?php checked( '1', $checked ); ?> >
			<?php echo $args['title']; ?>
		</label>
		<?php
	}

	public function get_oauth_token() {
		$provider = new Git(
			[
				'clientId'     => '{github-client-id}',
				'clientSecret' => '{github-client-secret}',
				'redirectUri'  => home_url() . $_SERVER['REQUEST_URI'],
			]
		);

		if ( ! isset( $_GET['code'] ) ) {

			// If we don't have an authorization code then get one
			$authUrl                 = $provider->getAuthorizationUrl();
			$_SESSION['oauth2state'] = $provider->getState();
			header( 'Location: ' . $authUrl );
			exit;

			// Check given state against previously stored one to mitigate CSRF attack
		} elseif ( empty( $_GET['state'] ) || ( $_GET['state'] !== $_SESSION['oauth2state'] ) ) {

			unset( $_SESSION['oauth2state'] );
			exit( 'Invalid state' );

		} else {

			// Try to get an access token (using the authorization code grant)
			$token = $provider->getAccessToken( 'authorization_code', [ 'code' => $_GET['code'] ] );

			// Optional: Now you have a token you can look up a users profile data
			try {

				// We got an access token, let's now get the user's details
				$user = $provider->getResourceOwner( $token );

				// Use these details to create a new profile
				printf( 'Hello %s!', $user->getNickname() );

			} catch ( \Exception $e ) {

				// Failed to get user details
				exit( 'Oh dear...' );
			}

			// Use this to interact with an API on the users behalf
			echo $token->getToken();
		}
	}
}
