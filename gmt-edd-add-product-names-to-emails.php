<?php

/**
 * Plugin Name: GMT EDD Add Product Names to Emails
 * Plugin URI: https://github.com/cferdinandi/gmt-edd-add-product-names-to-emails/
 * GitHub Plugin URI: https://github.com/cferdinandi/gmt-edd-add-product-names-to-emails/
 * Description: Add WP Rest API hooks into Easy Digital Downloads.
 * Version: 0.1.0
 * Author: Chris Ferdinandi
 * Author URI: http://gomakethings.com
 * License: GPLv3
 */


	/**
	 * Get an array of download names for a given purchase
	 *
	 * @since       1.0.0
	 * @param       int $payment_id The ID of a given purchase
	 * @return      array $files The array of files
	 */
	function gmt_edd_apnte_get_downloads( $payment_id = 0 ) {
		$cart   = edd_get_payment_meta_cart_details( $payment_id, true );
		$files  = array();

		if( $cart ) {
			foreach( $cart as $key => $item ) {
				if( empty( $item['in_bundle'] ) ) {
					$files[] = $item['name'];
				}
			}
		}

		return $files;
	}



	/**
	 * Add tag to email templates
	 * @param  Number $payment_id The payment ID
	 * @return Array  The download all tag
	 */
	function gmt_edd_apnte_setup_email_tags( $payment_id ) {
		edd_add_email_tag( 'download_list_names', __( 'Adds a comma-separated listed of purchased product names', 'edd' ), 'gmt_edd_apnte_get_download_list_names' );
	}
	add_action( 'edd_add_email_tags', 'gmt_edd_apnte_setup_email_tags' );



	/**
	 * Get a list of download names for the email
	 * @param  Number $payment_id The payment ID
	 * @return String             A comma-separated list of download names
	 */
	function gmt_edd_apnte_get_download_list_names( $payment_id ) {

		// Variables
		$files = gmt_edd_apnte_get_downloads( $payment_id );

		return implode( ', ', $files );

	}