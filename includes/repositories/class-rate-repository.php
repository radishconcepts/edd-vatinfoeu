<?php

class VIEU_Rate_Repository extends VIEU_Abstract_Repository {
	public function get_rates( $country_id, $category_id = null ) {
			return $this->query_rates( $country_id, $category_id );
	}

	private function query_rates( $country_id, $category_id = null ) {
		$handler = new VIEU_EDD_API_Handler();
		$arguments = array(
			'category' => intval( $category_id ),
			'country' => intval( $country_id ),
		);

		$data = $handler->handle_request( 'rate', $arguments );

		$return_array = array();

		if ( $this->is_ok_response( $data ) ) {
			$rates = json_decode( $data['body'] )->data;

			foreach ( $rates as $rate ) {
				$return_array[] = $rate;
			}
		}

		return $return_array;
	}
}