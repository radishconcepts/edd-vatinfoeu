<?php

class EDD_VAT_Info_EU {
	public function __construct() {
		if ( is_admin() ) {
			new EDD_VIEU_Admin_Tax_Settings();
			new EDD_VIEU_Admin_Order_Meta();
		}

		new EDD_VIEU_Checkout();
		new EDD_VIEU_Tax();
	}
}