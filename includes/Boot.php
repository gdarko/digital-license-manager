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
		 * Library: Micromodal
		 */
		wp_register_style( 'dlm_micromodal', DLM_CSS_URL . 'lib/micromodal.css' );
		wp_register_script( 'dlm_micromodal', DLM_JS_URL . 'lib/micromodal.min.js' );

		/**
		 * Library: Select2
		 */
		wp_register_style( 'dlm_select2', DLM_CSS_URL . 'lib/select2.min.css', array(), '4.0.13' );
		wp_register_script( 'dlm_select2', DLM_JS_URL . 'lib/select2.min.js', array( 'jquery' ), '4.0.13' );
		wp_register_style( 'dlm_select2_custom', DLM_CSS_URL . 'select2.css' );

		/**
		 * Page specific
		 */
		wp_register_script( 'dlm_licenses_page', DLM_JS_URL . 'licenses.js', array( 'jquery' ), $this->version );
		wp_register_script( 'dlm_generators_page', DLM_JS_URL . 'generators.js', array( 'jquery' ), $this->version );
		wp_register_script( 'dlm_activations_page', DLM_JS_URL . 'activations.js', array( 'jquery' ), $this->version );
		wp_register_script( 'dlm_settings_page', DLM_JS_URL . 'settings.js', array( 'jquery' ), $this->version );
		wp_register_style( 'dlm_settings_page', DLM_CSS_URL . 'settings.css', array(), $this->version, 'all' );
		wp_register_script( 'dlm_products_page', DLM_JS_URL . 'products.js', array( 'jquery' ), $this->version );

		/**
		 * Global assets
		 */
		wp_register_style( 'dlm_main', DLM_CSS_URL . 'main.css', array(), $this->version );
		wp_register_script( 'dlm_main', DLM_JS_URL . 'main.js', array( 'jquery' ), $this->version );

		/**
		 * jQuery UI: Stylesheet
		 */
		wp_register_style( 'dlm_jquery-ui-datepicker', DLM_CSS_URL . 'lib/jquery-ui.min.css', array(), '1.12.1' );
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

		/**
		 * Global assets
		 */
		wp_enqueue_style( 'dlm_jquery-ui-datepicker' );
		wp_enqueue_style( 'dlm_main' );
		wp_enqueue_script( 'dlm_main' );
		wp_localize_script(
			'dlm_main',
			'DLM_MAIN',
			array(
				'show'              => wp_create_nonce( 'dlm_show_license_key' ),
				'show_all'          => wp_create_nonce( 'dlm_show_all_license_keys' ),
				'product_downloads' => Settings::get( 'product_downloads' ),
				'i18n'              => array(
					'confirm_dialog' => __( 'Are you sure? This action can not be reverted.', 'digital-license-manager' )
				)
			)
		);

		/**
		 * Enqueue select2
		 */
		if ( $isLicenses || $isGenerators || $isSettings || $isActivations ) {
			wp_enqueue_script( 'dlm_select2' );
			wp_enqueue_style( 'dlm_select2' );
			wp_enqueue_style( 'dlm_select2_custom' );
		}

		/**
		 * Page: Licenses
		 */
		if ( $isLicenses ) {
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
					'placeholderFilterByUser'    => __( 'Filter by user', 'digital-license-manager' )
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
			wp_enqueue_media();
			wp_enqueue_script( 'dlm_settings_page' );
			wp_enqueue_style('dlm_settings_page');
		}

		/**
		 * Page: Products
		 */
		if ( $isProducts ) {
			wp_enqueue_script( 'dlm_products_page' );
		}
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

		add_action( 'admin_enqueue_scripts', array( $this, 'registerAssets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'registerAssets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'adminEnqueueScripts' ) );
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

		new DropdownsController();
		new LicenseController();
		new GeneratorController();
		new ApiKeyController();
		new WelcomeController();

		if ( CompatibilityHelper::is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			new WooCommerceController();
		}
		new RestController();

		do_action( 'dlm_init' );
	}

	/**
	 * Init the admin interface/settings.
	 */
	public function adminInit() {
		SettingsController::instance()->register();
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
