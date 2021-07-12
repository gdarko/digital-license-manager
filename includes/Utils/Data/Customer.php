<?php


namespace IdeoLogix\DigitalLicenseManager\Utils\Data;


use IdeoLogix\DigitalLicenseManager\Database\Models\Resources\License as LicenseResourceModel;
use IdeoLogix\DigitalLicenseManager\Database\Repositories\Resources\License as LicenseResourceRepository;
use WC_Order;

/**
 * Class Customer
 * @package IdeoLogix\DigitalLicenseManager\Utils\Data
 */
class Customer {

	/**
	 * Retrieves all license keys for a user and groups them by product
	 *
	 * @param int $userId
	 *
	 * @return WC_Order[]
	 */
	public static function getLicenseKeys( $userId ) {

		if ( ! function_exists( 'wc_get_product' ) ) {
			return array();
		}

		global $wpdb;
		$query = "
            SELECT
                DISTINCT(pm1.post_id) AS orderId
            FROM
                {$wpdb->postmeta} AS pm1
            INNER JOIN
                {$wpdb->postmeta} AS pm2
                ON 1=1
                   AND pm1.post_id = pm2.post_id
            WHERE
                1=1
                AND pm1.meta_key = 'dlm_order_complete'
                AND pm1.meta_value = '1'
                AND pm2.meta_key = '_customer_user'
                AND pm2.meta_value = '{$userId}'
        ;";

		$result   = array();
		$orderIds = $wpdb->get_col( $query );

		if ( empty( $orderIds ) ) {
			return array();
		}

		/** @var LicenseResourceModel[] $licenses */
		$licenses = LicenseResourceRepository::instance()->findAllBy(
			array(
				'order_id' => $orderIds
			)
		);

		foreach ( $licenses as $license ) {
			$product = wc_get_product( $license->getProductId() );
			if ( ! $product ) {
				$result[ $license->getProductId() ]['name'] = '#' . $license->getProductId();
			} else {
				$result[ $license->getProductId() ]['name'] = $product->get_formatted_name();
			}
			$result[ $license->getProductId() ]['licenses'][] = $license;
		}

		return $result;
	}

}