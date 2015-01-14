<?php

class EDD_VIEU_Checkout {
	/** @var array */
	private $countries = array();

	public function __construct() {
		$country_repo = new VIEU_Country_Repository();
		$this->countries = $country_repo->get_countries();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_checkout_script' ) );
		add_action( 'edd_cc_billing_bottom', array( $this, 'add_vat_number_field' ) );
		add_filter( 'edd_checkout_error_checks', array( $this, 'check_vat_number' ), 10, 2 );

		add_action( 'wp_ajax_euvi_maybe_vat_exempt', array( $this, 'maybe_vat_exempt' ) );
		add_action( 'wp_ajax_nopriv_euvi_maybe_vat_exempt', array( $this, 'maybe_vat_exempt' ) );

		add_filter( 'edd_payment_meta', array( $this, 'store_order_data' ) );
	}

	public function maybe_vat_exempt() {
		$this->reset();

		$billing_country = ( empty( $_POST['billing_country'] ) ) ? edd_get_shop_country() : $_POST['billing_country'];
		$vat_number = $_POST['vat_number'];

		if ( $this->validate( $vat_number, $billing_country ) ) {
			$this->set_vat_exempt();
		}
	}

	public function enqueue_checkout_script() {
		$parts = explode( '/', dirname( VIEU_PLUGIN_FILE_PATH ) );
		$plugin_slug = array_pop( $parts );
		$plugin_url = trailingslashit( plugins_url( $plugin_slug ) );
		wp_enqueue_script( 'euvi-checkout', $plugin_url . 'assets/euvi-checkout.js', array('edd-checkout-global') );
	}

	public function add_vat_number_field() {
		?>
			<p id="edd-vieu-vat-number-wrap">
				<label for="vieu_vat_number" class="edd-label">
					VAT Number
				</label>
				<span class="edd-description">Enter your company VAT number if you want to be VAT exempted.</span>
				<input type="text" size="6" name="vieu_vat_number" id="vieu_vat_number" class="vieu_vat_number edd-input" placeholder="VAT Number"/>
			</p>
	<?php
	}

	public function check_vat_number( $valid_data, $data ) {
		$this->reset();

		if ( isset( $data['vieu_vat_number'] ) && ! empty( $data['vieu_vat_number'] ) ) {
			if ( ! $this->validate( $data['vieu_vat_number'], $data['billing_country'] ) ) {
				edd_set_error( 'euvi-invalid-vat', sprintf( 'The VAT number (%s) is invalid for your billing country.', $data['vieu_vat_number'] ) );
				return;
			}

			$this->set_vat_exempt();
		}
	}

	public function store_order_data( $payment_meta ) {
		if ( isset( $_POST['vieu_vat_number'] ) && ! empty( $_POST['vieu_vat_number'] ) ) {
			$payment_meta['vat_number'] = $_POST['vieu_vat_number'];
		} else {
			$payment_meta['vat_number'] = "";
		}

		return $payment_meta;
	}

	private function set_vat_exempt() {
		EDD()->session->set( 'euvi_vat_exempt', true );
	}

	private function reset() {
		EDD()->session->set( 'euvi_vat_exempt', false );
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