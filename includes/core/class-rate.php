<?php

class VIEU_Rate {
	public function get_rate( $country_id, $category_id ) {
		$repository = new VIEU_Rate_Repository();
		$rates = $repository->get_rates( $country_id, $category_id );

		$highest_rate_key = 0;

		foreach ( $rates as $key => $value ) {
			if ( 0 == $highest_rate_key ) {
				$highest_rate_key = $key;
			} else {
				if ( $rates[ $highest_rate_key ] < $rates[ $key ] ) {
					$highest_rate_key = $key;
				}
			}
		}

		return $rates[ $highest_rate_key];
	}
}