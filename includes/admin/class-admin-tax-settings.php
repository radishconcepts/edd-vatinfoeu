<?php

class EDD_VIEU_Admin_Tax_Settings {
	public function __construct() {
		add_filter( 'edd_settings_taxes', array( $this, 'tax_settings' ), 10, 1 );
	}

	public function tax_settings( $settings ) {
		$edd_options = get_option('edd_settings');

		$categories = array();
		$categories[0] = 'Select your category (optional)';

		if ( isset( $edd_options['vieu_api_key'] ) && ! empty( $edd_options['vieu_api_key'] ) ) {
			$category_repo = new VIEU_Category_Repository();

			foreach ( $category_repo->get_categories() as $category ) {
				$categories[ $category->id ] = $category->name;
			}
		}

		$settings_array = array(
			'vieu_enabled' => array(
				'id' => 'vieu_enabled',
				'name' => 'EU VAT Info enabled',
				'desc' => 'Enable the tax rate calculation via the EU VAT Info API for customers within the European Union.',
				'type' => 'checkbox',
				'default' => false,
			),
			'vieu_api_key' => array(
				'id' => 'vieu_api_key',
				'name' => 'EU VAT Info API key',
				'desc' => 'Enter your API key as provided when you ordered your subscription at <a href="http://vatinfo.eu">vatinfo.eu</a>.',
				'type' => 'text',
			),
			'vieu_category' => array(
				'id' => 'vieu_category',
				'name' => 'EU VAT Info category',
				'desc' => 'The rate category that should be used to determine tax rates for your products. If none specified, the standard rates of your customers country will be used. This list will only be populated when a valid API key is set.',
				'type' => 'select',
				'options' => $categories,
			)
		);

		return array_merge( array_slice($settings, 0, 2, true), $settings_array, array_slice($settings, 2, count($settings)-2, false) );
	}
}