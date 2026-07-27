<?php
/**
 * React SPA admin page: asset loading and bootstrap data injection.
 *
 * @package Miniorange_Secure_MCP_Server
 */

namespace MoSMCP\Common\Views\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MoSMCP\Common\Utils\Utils;

/**
 * Class Admin_App
 *
 * Renders the single-page React application for the plugin admin UI.
 * In development the Vite dev server on port 5173 is used automatically;
 * in production assets are loaded from views/build/assets/.
 */
class Admin_App {

	/**
	 * WordPress admin page slug (admin, manage_options).
	 */
	const PAGE_SLUG = 'mosmcp-abilities';

	/**
	 * Member page slug (any logged-in user, `read`). Shown to non-admins so the
	 * admin surface is never exposed to them; the SPA renders the member view.
	 */
	const MEMBER_PAGE_SLUG = 'mosmcp-access';


	/**
	 * Script handle used for `wp_add_inline_script`.
	 */
	const SCRIPT_HANDLE = 'mosmcp-app';

	/**
	 * Registers admin-page hooks. Called from {@see Hooks::init()}.
	 *
	 * @return void
	 */
	public static function register_hooks() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Enqueues (or schedules) assets for the plugin admin page.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 * @return void
	 */
	public static function enqueue_assets( $hook_suffix ) {
		if ( 'toplevel_page_' . self::PAGE_SLUG !== $hook_suffix && 'toplevel_page_' . self::MEMBER_PAGE_SLUG !== $hook_suffix ) {
			return;
		}

		// Enqueue the production build if it exists.
		self::enqueue_prod_assets();

		// Bootstrap payload always printed before the app scripts.
		add_action( 'admin_footer', array( __CLASS__, 'print_bootstrap' ), 1 );
	}

	/**
	 * Renders the React mount point.
	 *
	 * @return void
	 */
	public static function render() {
		// The menu registration gates each slug by capability (admin vs member);
		// any logged-in user may reach the mount, and the SPA decides which view to
		// render from the `isAdmin` flag in the bootstrap payload.
		if ( ! is_user_logged_in() ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'miniorange-secure-mcp-server' ) );
		}

		echo '<div id="mosmcp-app-root"></div>';
	}


	/**
	 * Enqueues the compiled IIFE bundle and its extracted CSS.
	 *
	 * No-op when the production build does not exist (i.e. during development).
	 *
	 * @return void
	 */
	private static function enqueue_prod_assets() {
		$js_path   = MOSMCP_PLUGIN_DIR . 'views/build/assets/index.js';
		$css_path  = MOSMCP_PLUGIN_DIR . 'views/build/assets/index.css';
		$build_url = plugins_url( 'views/build/assets/', MOSMCP_PLUGIN_FILE );

		if ( ! file_exists( $js_path ) ) {
			return;
		}

		// Version by file mtime so a rebuilt bundle busts the browser cache even
		// when the plugin version is unchanged.
		$js_ver  = (string) filemtime( $js_path );
		$css_ver = file_exists( $css_path ) ? (string) filemtime( $css_path ) : $js_ver;

		if ( file_exists( $css_path ) ) {
			wp_enqueue_style(
				self::SCRIPT_HANDLE . '-css',
				$build_url . 'index.css',
				array(),
				$css_ver
			);
		}

		wp_enqueue_script(
			self::SCRIPT_HANDLE,
			$build_url . 'index.js',
			array(),
			$js_ver,
			true
		);
	}

	/**
	 * Prints the `window.MOSMCP_BOOTSTRAP` inline script.
	 *
	 * @return void
	 */
	public static function print_bootstrap() {
		$bootstrap = self::build_bootstrap();
		$json      = wp_json_encode( $bootstrap );

		echo '<script>window.MOSMCP_BOOTSTRAP=' . $json . ';</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON-encoded by wp_json_encode.
	}

	/**
	 * Builds the bootstrap payload injected into `window.MOSMCP_BOOTSTRAP`.
	 *
	 * @return array<string, mixed>
	 */
	private static function build_bootstrap() {
		$user = wp_get_current_user();

		return array(
			'restRoot'      => esc_url_raw( rest_url() ),
			'restNamespace' => MOSMCP_REST_NAMESPACE,
			'nonce'         => wp_create_nonce( 'wp_rest' ),
			'pluginUrl'     => esc_url_raw( plugins_url( '/', MOSMCP_PLUGIN_FILE ) ),
			'version'       => MOSMCP_VERSION,
			'mcpEndpoint'   => Utils::resource_url(),
			'migratedVersion' => current_user_can( 'manage_options' ) ? (string) get_option( 'mosmcp_migrated_version', '' ) : '',
			'user'          => array(
				'id'          => (int) $user->ID,
				'displayName' => $user->display_name,
				'isAdmin'     => current_user_can( 'manage_options' ),
				'roles'       => array_values( (array) $user->roles ),
			),
			'abilities'     => self::get_abilities_payload(),
		);
	}

	/**
	 * Returns a JSON-serializable list of registered abilities.
	 *
	 * @return list<array<string, string>>
	 */
	private static function get_abilities_payload() {
		if ( ! function_exists( 'wp_get_abilities' ) ) {
			return array();
		}

		$raw    = wp_get_abilities();
		$result = array();

		foreach ( $raw as $ability ) {
			$category = '';
			if ( method_exists( $ability, 'get_category' ) ) {
				$category = (string) $ability->get_category();
			} elseif ( method_exists( $ability, 'get_meta_item' ) ) {
				$meta_cat = $ability->get_meta_item( 'category' );
				$category = is_string( $meta_cat ) ? $meta_cat : '';
			}

			$required_cap = '';
			if ( method_exists( $ability, 'get_meta_item' ) ) {
				$rc           = $ability->get_meta_item( 'required_cap' );
				$required_cap = is_string( $rc ) ? $rc : '';
			}

			$result[] = array(
				'name'         => $ability->get_name(),
				'label'        => $ability->get_label(),
				'description'  => $ability->get_description(),
				'category'     => $category,
				'required_cap' => $required_cap,
			);
		}

		return $result;
	}

}
