<?php

namespace IdeoLogix\DigitalLicenseManager;

use IdeoLogix\DigitalLicenseManager\Abstracts\Singleton;
use IdeoLogix\DigitalLicenseManager\Controllers\ApiKeys as ApiKeyController;
use IdeoLogix\DigitalLicenseManager\Controllers\Dropdowns as DropdownsController;
use IdeoLogix\DigitalLicenseManager\Controllers\Generators as GeneratorController;
use IdeoLogix\DigitalLicenseManager\Controllers\Licenses as LicenseController;
use IdeoLogix\DigitalLicenseManager\Controllers\Menus as MenuController;
use IdeoLogix\DigitalLicenseManager\Controllers\Settings as SettingsController;
use IdeoLogix\DigitalLicenseManager\Controllers\Welcome as WelcomeController;
use IdeoLogix\DigitalLicenseManager\Integrations\WooCommerce\Controller as WooCommerceController;
use IdeoLogix\DigitalLicenseManager\RestAPI\Setup as RestController;
use IdeoLogix\DigitalLicenseManager\Utils\CompatibilityHelper;
use IdeoLogix\DigitalLicenseManager\Utils\CryptoHelper;
use IdeoLogix\DigitalLicenseManager\Utils\DateFormatter;
use IdeoLogix\DigitalLicenseManager\Utils\NoticeFlasher;
use IdeoLogix\DigitalLicenseManager\Utils\NoticeManager;

defined( 'ABSPATH' ) || exit;

/**
 * Class Boot
 * @package IdeoLogix\DigitalLicenseManager
 */
class Boot extends Singleton {
	/**
	 * @var string
	 */
	public $version;

	/**
	 * @var LicenseController
	 */
	public $licenses;

	/**
	 * @var DropdownsController
	 */
	public $dropdowns;

	/**
	 * @var GeneratorController
	 */
	public $generators;

	/**
	 * @var ApiKeyController
	 */
	public $api_keys;

	/**
	 * @var WooCommerceController
	 */
	public $woocommerce;

	/**
	 * @var WelcomeController
	 */
	public $welcome;

	/**
	 * @var RestController
	 */
	public $rest;

	/**
	 * Main constructor.
	 *
	 * @return void
	 */
	public function __construct() {

		$this->_defineConstants();

		$this->version = DLM_VERSION;

		$this->_initHooks();

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'adminInit' ) );

		new RestAPI\Authentication();

		// Init other plugins dependant on DLM
		do_action( 'dlm_boot' );

	}

	/**
	 * Define plugin constants.
	 *
	 * @return void
	 */
	private function _defineConstants() {

		if ( ! defined( 'ABSPATH_LENGTH' ) ) {
			define( 'ABSPATH_LENGTH', strlen( ABSPATH ) );
		}
		if ( ! defined( 'DLM_PLUGIN_FILE' ) ) {
			define( 'DLM_PLUGIN_FILE', plugin_dir_path( dirname( __FILE__ ) ) . 'digital-license-manager.php' );  // One level backwards, for composer support.
		}
		if ( ! defined( 'DLM_PLUGIN_URL' ) ) {
			define( 'DLM_PLUGIN_URL', plugin_dir_url( dirname( __FILE__ ) ) ); // One level backwards, for composer support.
		}
		if ( ! defined( 'DLM_ABSPATH' ) ) {
			define( 'DLM_ABSPATH', trailingslashit( dirname( DLM_PLUGIN_FILE ) ) );
		}
		if ( ! defined( 'DLM_VERSION' ) ) {
			define( 'DLM_SHORT_INIT', true ); // Just a short init, only the DLM_VERSION constant is initialized in this call.
			require_once DLM_PLUGIN_FILE;
		}

		define( 'DLM_PLUGIN_BASENAME', plugin_basename( DLM_PLUGIN_FILE ) );

		// Directories
		define( 'DLM_ASSETS_DIR', DLM_ABSPATH . 'assets' . DIRECTORY_SEPARATOR );
		define( 'DLM_TEMPLATES_DIR', DLM_ABSPATH . 'templates' . DIRECTORY_SEPARATOR );
		define( 'DLM_MIGRATIONS_DIR', DLM_ABSPATH . 'migrations' . DIRECTORY_SEPARATOR );

		// URL's
		define( 'DLM_ASSETS_URL', DLM_PLUGIN_URL . 'assets/' );
		define( 'DLM_CSS_URL', DLM_ASSETS_URL . 'css/' );
		define( 'DLM_JS_URL', DLM_ASSETS_URL . 'js/' );
		define( 'DLM_IMG_URL', DLM_ASSETS_URL . 'img/' );
	}

	/**
	 * Register JS/CSS assets.
	 */
	public function registerAssets() {

		/**
		 * Library: Flatpickr
		 */
		wp_register_style( 'dlm_flatpickr', DLM_ASSETS_URL . 'lib/flatpickr/flatpickr.min.css' );
		wp_register_script( 'dlm_flatpickr', DLM_ASSETS_URL . 'lib/flatpickr/flatpickr.min.js' );

		/**
		 * Library: Micromodal
		 */
		wp_register_style( 'dlm_micromodal', DLM_ASSETS_URL . 'lib/micromodal/micromodal.css' );
		wp_register_script( 'dlm_micromodal', DLM_ASSETS_URL . 'lib/micromodal/micromodal.min.js' );

		/**
		 * Library: Tom-Select.js
		 */
		wp_register_script( 'dlm_tomselect', DLM_ASSETS_URL . 'lib/tom-select/tom-select.complete.min.js', [], '2.2.2' );
		wp_register_style( 'dlm_tomselect', DLM_ASSETS_URL . 'lib/tom-select/tom-select.default.min.css', [], '2.2.2' );

		/**
		 * Internal Library: HTTP
		 */
		wp_register_script( 'dlm_utils', DLM_ASSETS_URL . 'js/shared/utils.js', [], $this->version );
		wp_register_script( 'dlm_http', DLM_ASSETS_URL . 'js/shared/http.js', [], $this->version );

		/**
		 * Internal Library: Select
		 */
		wp_register_script( 'dlm_select', DLM_ASSETS_URL . 'js/shared/select.js', [ 'dlm_tomselect', 'dlm_http' ], $this->version );
		wp_register_style( 'dlm_select', DLM_ASSETS_URL . 'css/shared/select.css', [ 'dlm_tomselect' ], $this->version );
		wp_localize_script( 'dlm_select', 'dlm_select_i18n', [ 'loading' => __( 'Loading more results...', 'digital-license-manager' ) ] );


		/**
		 * Library: Fontello icons
		 */
		wp_register_style( 'dlm_iconfont', DLM_ASSETS_URL . 'lib/iconfont/css/digital-license-manager.css' );

		/**
		 * Page specific
		 */
		wp_register_script( 'dlm_licenses_page', DLM_JS_URL . 'admin/licenses.js', array( 'jquery', 'dlm_select', 'dlm_flatpickr' ), $this->version );
		wp_register_script( 'dlm_generators_page', DLM_JS_URL . 'admin/generators.js', array( 'dlm_select' ), $this->version );
		wp_register_script( 'dlm_activations_page', DLM_JS_URL . 'admin/activations.js', array( 'dlm_select' ), $this->version );
		wp_register_script( 'dlm_settings_page', DLM_JS_URL . 'admin/settings.js', array( 'dlm_utils' ), $this->version );
		wp_register_style( 'dlm_settings_page', DLM_CSS_URL . 'admin/settings.css', array(), $this->version, 'all' );
		wp_register_script( 'dlm_products_page', DLM_JS_URL . 'admin/products.js', array(), $this->version );
		wp_register_style( 'dlm_manage_page', DLM_CSS_URL . 'admin/manage.css', array(), $this->version, 'all' );
		wp_register_script( 'dlm_tools_page', DLM_JS_URL . 'admin/tools.js', array( 'dlm_http' ), $this->version );
		wp_register_style( 'dlm_tools_page', DLM_CSS_URL . 'admin/tools.css', array(), $this->version, 'all' );


		/**
		 * Global assets
		 */
		wp_register_style( 'dlm_global', DLM_CSS_URL . 'global.css', array(), $this->version );

		/**
		 * Global admin assets
		 */
		wp_register_style( 'dlm_admin', DLM_CSS_URL . 'admin/general.css', array(), $this->version );
		wp_register_script( 'dlm_admin', DLM_JS_URL . 'admin/general.js', array( 'dlm_http', 'dlm_select' ), $this->version );

		do_action( 'dlm_register_scripts', $this->version );
	}

	/**
	 * Include JS and CSS files.
	 *
	 * @param string $hook
	 *
	 * @return void
	 */
	public function adminEnqueueScripts( $hook ) {

		global $post_type;

		// Conditionals
		$isLicenses    = $hook === 'toplevel_page_dlm_licenses';
		$isGenerators  = $hook === 'license-manager_page_dlm_generators';
		$isActivations = $hook === 'license-manager_page_dlm_activations';
		$isSettings    = $hook === 'license-manager_page_dlm_settings';
		$isProducts    = in_array( $hook, array( 'post.php', 'post-new.php' ) ) && 'product' === $post_type;
		$isOrder       = in_array( $hook, array( 'post.php', 'post-new.php' ) ) && 'shop_order' === $post_type;
		$isManage      = $isLicenses || $isGenerators || $isActivations || apply_filters( 'dlm_admin_stylesheet_is_manage', false );

		/**
		 * Global assets
		 */
		wp_enqueue_style( 'dlm_global' );
		wp_enqueue_style( 'dlm_iconfont' );

		/**
		 * Global Admin assets
		 */
		wp_enqueue_style( 'dlm_admin' );
		wp_enqueue_script( 'dlm_admin' );
		wp_localize_script(
			'dlm_admin',
			'DLM_MAIN',
			array(
				'show'              => wp_create_nonce( 'dlm_show_license_key' ),
				'show_all'          => wp_create_nonce( 'dlm_show_all_license_keys' ),
				'product_downloads' => Settings::get( 'product_downloads' ),
				'security' => array(
					'dropdownSearch' => wp_create_nonce('dlm_dropdown_search')
				),
				'i18n'              => array(
					'confirm_dialog' => __( 'Are you sure? This action can not be reverted.', 'digital-license-manager' ),
					'placeholderSearchUsers'     => __( 'Search by user login, name or email', 'digital-license-manager' ),
				)
			)
		);

		/**
		 * Enqueue css on the create/edit pages
		 */
		if ( $isManage ) {
			wp_enqueue_style( 'dlm_manage_page' );
		}

		/**
		 * Page: Licenses
		 */
		if ( $isLicenses || $isOrder ) {
			$dateFormat = get_option( 'date_format' );
			$timeFormat = DateFormatter::convertTimeFormatForFlatpickr( get_option( 'time_format' ) );

			wp_enqueue_style( 'dlm_select' );
			wp_enqueue_style( 'dlm_flatpickr' );
			wp_enqueue_style( 'dlm_micromodal' );
			wp_enqueue_script( 'dlm_micromodal' );
			wp_enqueue_script( 'dlm_licenses_page' );
			wp_localize_script(
				'dlm_licenses_page',
				'dlm_licenses_i18n',
				array(
					'placeholderSearchOrders'    => __( 'Search by order ID or email', 'digital-license-manager' ),
					'placeholderSearchProducts'  => __( 'Search by product ID or name', 'digital-license-manager' ),
					'placeholderSearchUsers'     => __( 'Search by user login, name or email', 'digital-license-manager' ),
					'placeholderFilterByOrder'   => __( 'Filter by order', 'digital-license-manager' ),
					'placeholderFilterByProduct' => __( 'Filter by product', 'digital-license-manager' ),
					'placeholderFilterByUser'    => __( 'Filter by user', 'digital-license-manager' ),
					'dateTimeFormat'             => sprintf( '%s at %s', $dateFormat, $timeFormat ),
					'copiedToClipboard'          => __( 'Copied to clipboard', 'digital-license-manager' )
				)
			);
			wp_localize_script(
				'dlm_licenses_page',
				'dlm_licenses_security',
				array(
					'dropdownSearch' => wp_create_nonce( 'dlm_dropdown_search' )
				)
			);
		}

		/**
		 * Page: Generators
		 */
		if ( $isGenerators ) {
			wp_enqueue_style( 'dlm_select' );
			wp_enqueue_style( 'dlm_flatpickr' );
			wp_enqueue_script( 'dlm_generators_page' );
			wp_localize_script(
				'dlm_generators_page',
				'dlm_generators_i18n',
				array(
					'placeholderSearchOrders'   => __( 'Search by order ID or customer email', 'digital-license-manager' ),
					'placeholderSearchProducts' => __( 'Search by product ID or product name', 'digital-license-manager' )
				)
			);
			wp_localize_script(
				'dlm_generators_page',
				'dlm_generators_security',
				array(
					'dropdownSearch' => wp_create_nonce( 'dlm_dropdown_search' )
				)
			);
		}

		if ( $isActivations ) {
			wp_enqueue_style( 'dlm_select' );
			wp_enqueue_style( 'dlm_flatpickr' );
			wp_enqueue_script( 'dlm_activations_page' );
			wp_localize_script(
				'dlm_activations_page',
				'dlm_activations_i18n',
				array(
					'placeholderSearchLicenses' => __( 'Search by license ID', 'digital-license-manager' ),
					'placeholderSearchSources'  => __( 'Search by source', 'digital-license-manager' ),
				)
			);
			wp_localize_script(
				'dlm_activations_page',
				'dlm_activations_security',
				array(
					'dropdownSearch' => wp_create_nonce( 'dlm_dropdown_search' )
				)
			);
		}

		/**
		 * Page: Settings
		 */
		if ( $isSettings ) {
			wp_enqueue_style( 'dlm_select' );
			wp_enqueue_style( 'dlm_flatpickr' );
			wp_enqueue_media();
			wp_enqueue_script( 'dlm_settings_page' );
			wp_enqueue_style( 'dlm_settings_page' );
			if ( isset( $_GET['tab'] ) && 'tools' === $_GET['tab'] ) {
				wp_enqueue_script( 'dlm_tools_page' );
				wp_enqueue_style( 'dlm_tools_page' );
				wp_localize_script( 'dlm_tools_page', 'DLM_Tools', array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'dlm-tools' ),
				) );
			}
		}

		/**
		 * Page: Products
		 */
		if ( $isProducts ) {
			wp_enqueue_script( 'dlm_products_page' );
		}
	}

	/**
	 * Enqueue public scripts
	 * @return void
	 */
	public function publicEnqueueScripts() {
		do_action( 'dlm_enqueue_scripts', $this->version );
	}


	/**
	 * Add additional links to the plugin row meta.
	 *
	 * @param array $links Array of already present links
	 * @param string $file File name
	 *
	 * @return array
	 */
	public function pluginRowMeta( $links, $file ) {
		if ( strpos( $file, DLM_PLUGIN_BASENAME ) !== false ) {
			$newLinks = array(
				'github' => sprintf(
					'<a href="%s" target="_blank">%s</a>',
					DLM_GITHUB_URL,
					__( 'GitHub', 'digital-license-manager' )
				),
				'docs'   => sprintf(
					'<a href="%s" target="_blank">%s</a>',
					DLM_DOCUMENTATION_URL,
					__( 'Documentation', 'digital-license-manager' )
				),
			);

			$links = array_merge( $links, $newLinks );
		}

		$coreBasename = str_replace( '-pro', '', DLM_PLUGIN_BASENAME );
		if ( $file === $coreBasename ) {
			$links[] = '<a style="font-weight:bold;color:#3eb03e;" target="_blank" href="' . DLM_PURCHASE_URL . '"><strong>' . __( 'Buy PRO Version', 'wp-vimeo-videos' ) . '</strong></a>';
		}

		return $links;
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @return void
	 */
	private function _initHooks() {

		register_activation_hook( DLM_PLUGIN_FILE, array( '\IdeoLogix\DigitalLicenseManager\Setup', 'install' ) );
		register_deactivation_hook( DLM_PLUGIN_FILE, array( '\IdeoLogix\DigitalLicenseManager\Setup', 'deactivate' ) );
		register_uninstall_hook( DLM_PLUGIN_FILE, array( '\IdeoLogix\DigitalLicenseManager\Setup', 'uninstall' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'registerAssets' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'registerAssets' ), 10 );
		add_action( 'admin_enqueue_scripts', array( $this, 'adminEnqueueScripts' ), 11 );
		add_action( 'wp_enqueue_scripts', array( $this, 'publicEnqueueScripts' ), 11 );
		add_filter( 'plugin_row_meta', array( $this, 'pluginRowMeta' ), 10, 2 );
	}

	/**
	 * Init IdeoLogix\DigitalLicenseManager when WordPress Initialises.
	 *
	 * @return void
	 */
	public function init() {
		Setup::migrate();

		new MenuController();

		CryptoHelper::instance();
		NoticeFlasher::instance();
		NoticeManager::instance();

		$this->dropdowns  = new DropdownsController();
		$this->licenses   = new LicenseController();
		$this->generators = new GeneratorController();
		$this->api_keys   = new ApiKeyController();
		$this->welcome    = new WelcomeController();

		if ( CompatibilityHelper::is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$this->woocommerce = new WooCommerceController();
		}
		$this->rest = new RestController();

		do_action( 'dlm_init', $this );
	}

	/**
	 * Init the admin interface/settings.
	 */
	public function adminInit() {
		SettingsController::instance()->register();

		if ( get_option( 'dlm_needs_permalinks_flush' ) ) {
			flush_rewrite_rules( false );
			delete_option( 'dlm_needs_permalinks_flush' );
		}
	}

	/**
	 * Checks if a plugin is active.
	 *
	 * @param string $pluginName
	 *
	 * @return bool
	 */
	private function isPluginActive( $pluginName ) {
		return in_array( $pluginName, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
	}
}
