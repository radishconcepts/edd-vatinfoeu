<?php

class EDD_VIEU_Checkout {
	public function __construct() {
		add_action('edd_cc_billing_bottom', array( $this, 'add_vat_number_field' ) );
	}

	public function add_vat_number_field() {
		?>
			<p id="edd-vieu-vat-number-wrap">
				<label for="vieu_vat_number" class="edd-label">
					VAT Number
				</label>
				<span class="edd-description">Your company VAT number if you want to excempt VAT.</span>
				<input type="text" size="6" name="vieu_vat_number" id="vieu_vat_number" class="vieu_vat_number edd-input" placeholder="VAT Number"/>
			</p>
	<?php
	}
}