<?php

abstract class VIEU_Abstract_Repository {
	/** @var string */
	protected $id;

	protected function is_ok_response( $data ) {
		if ( is_wp_error( $data ) ) {
			return false;
		}

		if ( ! isset( $data['response']['code'] ) || '200' != $data['response']['code'] ) {
			return false;
		}

		return true;
	}

	protected function save_data( $data ) {
		set_transient( 'vieu_' . $this->id . '_data', $data, DAY_IN_SECONDS );
	}

	protected function get_data() {
		$data = get_transient( 'vieu_' . $this->id . '_data' );

		if ( ! empty( $data ) ) {
			return $data;
		}

		return false;
	}
}