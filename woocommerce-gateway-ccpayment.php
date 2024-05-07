<?php
/**
 * Plugin Name: CCPayment.com Payment Gateway for WooCommerce
 * Plugin URI: https://github.com/cctip/woocommerce-gateway-ccpayment
 * Description: Adds the CCPayment Payments gateway to your WooCommerce website.
 * Version: 1.0.0
 *
 * Author: CCPayment
 * Author URI: https://ccpayment.com/
 *
 * Text Domain: ccpayment-payment-gateway-for-woocommerce
 * Domain Path: /i18n/languages/
 *
 * Requires at least: 4.2
 * Tested up to: 6.4
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC CCPayment Payment gateway plugin class.
 *
 * @class WC_CCPayment_Payments
 */
class WC_CCPayment_Payments {

	/**
	 * Plugin bootstrapping.
	 */
	public static function init() {

        define('CCPAYMENT_PLUGIN_PATH', __DIR__);
        define('CCPAYMENT_PLUGIN_DIR_NAME', basename(CCPAYMENT_PLUGIN_PATH));
        define('CCPAYMENT_PLUGIN_URL', plugins_url(CCPAYMENT_PLUGIN_DIR_NAME . '/'));
        define('CCPAYMENT_WOOCOMMERCE_VERSION', '1.0.0');
        define('CCPAYMENT_TYPE', 'ApiDeposit'); // ApiDeposit
        define('CCPAYMENT_NAME', 'ccpayment');
        define('CCPAYMENT_LOG_FILE', CCPAYMENT_PLUGIN_PATH.'/error.log');

		// CCPayment Payments gateway class.
		add_action( 'plugins_loaded', array( __CLASS__, 'includes' ), 0 );

		// Make the CCPayment Payments gateway available to WC.
		add_filter( 'woocommerce_payment_gateways', array( __CLASS__, 'add_gateway' ) );

		// Registers WooCommerce Blocks integration.
		add_action( 'woocommerce_blocks_loaded', array( __CLASS__, 'ccpayment_woocommerce_block_support') );

	}

	/**
	 * Add the Ccpayment Payment gateway to the list of available gateways.
	 *
	 * @param array
	 */
	public static function add_gateway( $gateways ) {

		$options = get_option( 'woocommerce_ccpayment_settings', array() );

		if ( isset( $options['hide_for_non_admin_users'] ) ) {
			$hide_for_non_admin_users = $options['hide_for_non_admin_users'];
		} else {
			$hide_for_non_admin_users = 'no';
		}

		if ( ( 'yes' === $hide_for_non_admin_users && current_user_can( 'manage_options' ) ) || 'no' === $hide_for_non_admin_users ) {
			$gateways[] = 'WC_Gateway_CCPayment';
		}
		return $gateways;
	}

	/**
	 * Plugin includes.
	 */
	public static function includes() {

		// Make the WC_Gateway_Ccpayment class available.
		if ( class_exists( 'WC_Payment_Gateway' ) ) {
			require_once 'includes/class-wc-gateway-ccpayment.php';
		}
	}

	/**
	 * Plugin url.
	 *
	 * @return string
	 */
	public static function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Plugin url.
	 *
	 * @return string
	 */
	public static function plugin_abspath() {
		return trailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Registers WooCommerce Blocks integration.
	 *
	 */
	public static function ccpayment_woocommerce_block_support() {
		if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
			require_once 'includes/blocks/class-wc-ccpayment-payments-blocks.php';
			add_action(
				'woocommerce_blocks_payment_method_type_registration',
				function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
					$payment_method_registry->register( new WC_Gateway_CCPayment_Blocks_Support() );
				}
			);
		}
	}

}

WC_CCPayment_Payments::init();

function infof($message): void
{
    error_log('['.gmdate('Y-m-d H:i:s').'][CCPayment] '.$message.PHP_EOL, 3, CCPAYMENT_LOG_FILE);
}

/**
 * @throws \Random\RandomException
 */
function randomString($length): string
{
    $bytes = random_bytes($length);
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $index = ord($bytes[$i]) % strlen($characters);
        $randomString .= $characters[$index];
    }
    return $randomString;
}