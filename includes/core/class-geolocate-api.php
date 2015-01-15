<?php

class VIEU_Geolocate_API {
	public function get_country_by_ip( $ip ) {
		$handler = new VIEU_EDD_API_Handler();
		$data    = $handler->handle_request( 'geolocate-ip', array(
			'ip' => $ip,
		) );

		if ( ! is_wp_error( $data ) && $data['body'] ) {
			$data = json_decode( $data['body'] );
			return $data->country_code;
		}

		return false;
	}
}