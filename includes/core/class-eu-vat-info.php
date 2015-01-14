<?php

class EDD_VAT_Info_EU {
	public function __construct() {
		if ( is_admin() ) {
			add_action('init', array( $this, 'init' ) );

			new EDD_VIEU_Admin_Tax_Settings();
			new EDD_VIEU_Admin_Order_Meta();
		}

		new EDD_VIEU_Checkout();
		new EDD_VIEU_Tax();
	}

	public function init() {
		$config = array(
			'slug' => plugin_basename(VIEU_PLUGIN_FILE_PATH),
			'proper_folder_name' => 'edd-vatinfoeu',
			'api_url' => 'https://api.github.com/repos/radishconcepts/edd-vatinfoeu',
			'raw_url' => 'https://raw.github.com/radishconcepts/edd-vatinfoeu/master',
			'github_url' => 'https://github.com/radishconcepts/edd-vatinfoeu',
			'zip_url' => 'https://github.com/radishconcepts/edd-vatinfoeu/zipball/master',
			'sslverify' => true,
			'requires' => '4.0',
			'tested' => '4.1',
			'readme' => 'README.md',
			'access_token' => '',
		);

		new WP_GitHub_Updater($config);
	}
}