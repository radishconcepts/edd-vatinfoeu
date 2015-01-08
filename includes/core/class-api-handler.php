<?php

class VIEU_EDD_API_Handler {
	private $endpoint;

	public function __construct() {
		$this->endpoint = apply_filters( 'radish_api_euvi_url', 'http://api.vatinfo.eu/api/v1/' );
	}

	public function handle_request( $target, $params = array() ) {
		$url = $this->generate_api_endpoint_url( $target, $params );
		$data = $this->get_request( $url, $params );

		return $data;
	}

	private function generate_api_endpoint_url( $target, $params ) {
		$url = trailingslashit( trailingslashit( $this->endpoint ) . $target );

		foreach ( $params as $key => $value ) {
			$url = add_query_arg( $key, $value, $url );
		}

		return $url;
	}

	private function get_request( $url, $params ) {
		$params['headers'] = array(
			'X-Auth-Token' => $this->get_api_key(),
		);
		return wp_remote_get( $url, $params );
	}

	private function get_api_key() {
		$edd_options = get_option('edd_settings');
		return $edd_options['vieu_api_key'];
	}
}