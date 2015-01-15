<?php

class VIEU_VAT_Validator {
	public function validate_vat($countryCode, $vatNumber) {
		$handler = new VIEU_EDD_API_Handler();
		$vatNumber = $this->get_formatted_vat_number( $vatNumber, $countryCode );

		$data = $handler->handle_request( 'validate-vat', array(
			'countryCode' => $countryCode,
			'vatNumber' => $vatNumber
		) );

		$response = json_decode( $data['body'] );
		return ($response->valid === true );
	}

	private function get_formatted_vat_number( $vat, $country ) {
		$vat = strtoupper( str_replace( array( ' ', '-', '_', '.' ), '', $vat ) );
		$prefix = $this->get_vat_number_prefix( $country );
		$vat = trim( $vat, $prefix );

		return $vat;
	}

	private function get_vat_number_prefix( $country ) {
		$vat_prefix = $country;

		// Some countries just want to watch the world burn...
		switch ( $country ) {
			case 'GR' :
				$vat_prefix = 'EL';
				break;
			case 'IM' :
				$vat_prefix = 'GB';
				break;
			case 'MC' :
				$vat_prefix = 'FR';
				break;
		}

		return $vat_prefix;
	}
}