<?php

class VIEU_Country_Repository extends VIEU_Abstract_Repository {
	private $countries = array();

	public function __construct() {
		$this->id = 'countries';
	}

	public function get_countries() {
		if ( empty( $this->countries ) ) {
			if ( false === $this->countries = $this->get_data() ) {
				$this->countries = $this->query_countries();
			}
		}

		return $this->countries;
	}

	public function get_country_by_code( $code ) {
		if ( empty( $this->countries ) ) {
			$this->countries = $this->query_countries();
		}

		foreach ( $this->countries as $country_key => $country ) {
			foreach ( $country->codes as $country_code ) {
				if ( $code == $country_code ) {
					return $this->countries[ $country_key ];
				}
			}
		}

		return false;
	}

	private function query_countries() {
		$handler = new VIEU_EDD_API_Handler();
		$data = $handler->handle_request( 'countries', array() );
		$return_array = array();

		if ( $this->is_ok_response( $data ) ) {
			$countries = json_decode( $data['body'] )->data;

			foreach ( $countries as $country ) {
				$return_array[] = $country;
			}

			$this->save_data($return_array);
			return $return_array;
		}

		return array();
	}
}