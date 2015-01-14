<?php

class EDD_VIEU_Checkout {
	private $countries = array();

	public function __construct() {
		$country_repo = new VIEU_Country_Repository();
		$this->countries = $country_repo->get_countries();

		add_action( 'edd_cc_billing_bottom', array( $this, 'add_vat_number_field' ) );
		add_filter( 'edd_checkout_error_checks', array( $this, 'check_vat_number' ), 10, 2 );
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

	public function check_vat_number( $valid_data, $data ) {
		if ( isset( $data['vieu_vat_number'] ) && ! empty( $data['vieu_vat_number'] ) ) {
			if ( ! $this->validate( $data['vieu_vat_number'], $data['billing_country'] ) ) {
				edd_set_error( 'euvi-invalid-vat', sprintf( 'The VAT number (%s) is invalid for your billing country.', $data['vieu_vat_number'] ) );
			}
		}
	}

	private function validate( $vat_number, $country_code ) {
		if ( ! $this->is_valid_eu_country( $country_code ) ) {
			return false;
		}

		$validator = new VIEU_VAT_Validator();
		return $validator->validate_vat($country_code, $vat_number);
	}

	private function is_valid_eu_country( $country_code ) {
		foreach ( $this->countries as $country ) {
			if ( $country->codes->alpha_2 == $country_code ) {
				return true;
			}
		}

		return false;
	}
}