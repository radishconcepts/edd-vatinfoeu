<?php

class EDD_VIEU_Tax {
	public function __construct() {
		add_filter( 'edd_tax_rate', array( $this, 'matched_tax_rates' ), 10, 2 );
	}

	public function matched_tax_rates( $rate, $country ) {
		$edd_settings = get_option('edd_settings');

		if ( isset( $edd_settings['vieu_enabled'] ) && '1' === $edd_settings['vieu_enabled'] ) {
			$country_repo = new VIEU_Country_Repository();
			$vieu_country = $country_repo->get_country_by_code( $country );

			// This method will return the default rate when country is not found in the API
			// In most cases this means that a country is not within the EU.
			if ( false === $vieu_country ) {
				return $rate;
			}

			$category_id = $edd_settings['vieu_category'];

			$rate_calc = new VIEU_Rate();
			$rate = $rate_calc->get_rate( $vieu_country->id, $category_id );
			$rate = $rate->rate / 100;
		}

		return $rate;
	}
}