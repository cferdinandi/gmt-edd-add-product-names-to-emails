<?php

/**
 * Plugin Name: GMT EDD Add Product Names to Emails
 * Plugin URI: https://github.com/cferdinandi/gmt-edd-add-product-names-to-emails/
 * GitHub Plugin URI: https://github.com/cferdinandi/gmt-edd-add-product-names-to-emails/
 * Description: Add WP Rest API hooks into Easy Digital Downloads.
 * Version: 0.3.0
 * Author: Chris Ferdinandi
 * Author URI: http://gomakethings.com
 * License: GPLv3
 */


	//
	// Settings
	//

	/**
	 * Add settings section
	 * @param array $sections The current sections
	 */
	function gmt_edd_apnte_settings_section( $sections ) {
		$sections['gmt_edd_apnte'] = __( 'Custom Email Tags', 'gmt_edd' );
		return $sections;
	}
	add_filter( 'edd_settings_sections_extensions', 'gmt_edd_apnte_settings_section' );


	/**
	 * Add settings
	 * @param  array $settings The existing settings
	 */
	function gmt_edd_apnte_settings( $settings ) {

		$slack_settings = array(
			array(
				'id'    => 'gmt_edd_apnte_settings',
				'name'  => '<strong>' . __( 'Email Settings', 'gmt_edd' ) . '</strong>',
				'desc'  => __( 'Email Settings', 'gmt_edd' ),
				'type'  => 'header',
			),
			array(
				'id'      => 'gmt_edd_apnte_membership_site',
				'name'    => __( 'Membership Site', 'gmt_edd' ),
				'desc'    => __( 'Your membership site messaging', 'gmt_edd' ),
				'type'    => 'text',
				'std'     => __( '', 'gmt_edd' ),
			),
		);
		if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
			$slack_settings = array( 'gmt_edd_apnte' => $slack_settings );
		}
		return array_merge( $settings, $slack_settings );
	}
	add_filter( 'edd_settings_extensions', 'gmt_edd_apnte_settings', 999, 1 );



	//
	// Helper Methods
	//

	/**
	 * Get an array of download names for a given purchase
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
	 * Get an array of download names for a given purchase
	 * @param       int $payment_id The ID of a given purchase
	 * @return      array $files The array of files
	 */
	function gmt_edd_apnte_downloads_have_membership_access( $payment_id = 0 ) {
		$cart   = edd_get_payment_meta_cart_details( $payment_id, true );

		if ( $cart ) {
			foreach( $cart as $key => $item ) {
				$item_id = explode( '_', $item['id'] );
				if ( has_term( 'membership-site', 'download_category',$item_id ) ) {
					return true;
				}
			}
		}

		return false;
	}


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


	/**
	 * Adds custom message if any items are part of membership site.
	 * @param  Number $payment_id The payment ID
	 * @return String             Custom message
	 */
	function gmt_edd_apnte_get_membership_message( $payment_id ) {

		// Variables
		$is_membership = gmt_edd_apnte_downloads_have_membership_access( $payment_id );

		if ($is_membership) {
			$message = edd_get_option( 'gmt_edd_apnte_membership_site', false );
			if ($message) {
				return $message;
			}
		}

		return '';

	}


	/**
	 * Get the purchase key
	 *
	 * @since       1.0.0
	 * @param       int $payment_id The ID of a given purchase
	 * @return      array $files The key
	 */
	function gmt_edd_apnte_get_purchase_key( $payment_id = 0 ) {
		$payment = new EDD_Payment( $payment_id );
		if (empty($payment->ID)) return '';
		return $payment->key;
	}



	//
	// Hooks and Filters
	//

	/**
	 * Add tag to email templates
	 * @param  Number $payment_id The payment ID
	 * @return Array  The download all tag
	 */
	function gmt_edd_apnte_setup_email_tags( $payment_id ) {
		edd_add_email_tag( 'download_list_names', __( 'Adds a comma-separated listed of purchased product names', 'edd' ), 'gmt_edd_apnte_get_download_list_names' );
		edd_add_email_tag( 'membership_site_message', __( 'Displays a custom message with purchases that include access to the membership site.', 'edd' ), 'gmt_edd_apnte_get_membership_message' );
		edd_add_email_tag( 'purchase_key', __( 'The purchase key.', 'edd' ), 'gmt_edd_apnte_get_purchase_key' );
	}
	add_action( 'edd_add_email_tags', 'gmt_edd_apnte_setup_email_tags' );