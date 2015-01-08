<?php

class VIEU_Type_Repository extends VIEU_Abstract_Repository {
	private $types = array();

	public function __construct() {
		$this->id = 'types';
	}

	public function get_types() {
		if ( empty( $this->types ) ) {
			if ( false === $this->types = $this->get_data() ) {
				$this->types = $this->query_types();
			}
		}

		return $this->types;
	}

	private function query_types() {
		$handler = new VIEU_EDD_API_Handler();
		$data = $handler->handle_request( 'types', array() );
		$return_array = array();

		if ( $this->is_ok_response( $data ) ) {
			$types = json_decode( $data['body'] )->data;

			foreach ( $types as $type ) {
				$return_array[ $type->id ] = $type;
			}
		}

		$this->save_data($return_array);

		return $return_array;
	}
}