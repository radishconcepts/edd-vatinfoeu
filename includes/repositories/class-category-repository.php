<?php

class VIEU_Category_Repository extends VIEU_Abstract_Repository {
	private $categories = array();

	public function __construct() {
		$this->id = 'categories';
	}

	public function get_categories() {
		if ( empty( $this->categories ) ) {
			if ( false === $this->categories = $this->get_data() ) {
				$this->categories = $this->query_categories();
			}
		}

		return $this->categories;
	}

	private function query_categories() {
		$handler = new VIEU_EDD_API_Handler();
		$data = $handler->handle_request( 'categories', array() );
		$return_array = array();

		if ( $this->is_ok_response( $data ) ) {
			$categories = json_decode( $data['body'] )->data;

			foreach ( $categories as $category ) {
				$return_array[] = $category;
			}

			$this->save_data($return_array);
			return $return_array;
		}

		return array();
	}
}