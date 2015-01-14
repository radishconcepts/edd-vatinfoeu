<?php

class EDD_VIEU_Checkout {
	/** @var array */
	private $countries = array();

	public function __construct() {
		$country_repo = new VIEU_Country_Repository();
		$this->countries = $country_repo->get_countries();

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_checkout_script' ) );
		add_action( 'edd_cc_billing_bottom', array( $this, 'add_vat_number_field' ) );
		add_action( 'edd_cc_billing_bottom', array( $this, 'location_confirmation' ) );
		add_filter( 'edd_checkout_error_checks', array( $this, 'validate_checkout' ), 10, 2 );

		add_action( 'wp_ajax_euvi_maybe_location_confirmation', array( $this, 'maybe_location_confirmation' ) );
		add_action( 'wp_ajax_nopriv_euvi_maybe_location_confirmation', array( $this, 'maybe_location_confirmation' ) );

		add_action( 'wp_ajax_euvi_maybe_vat_exempt', array( $this, 'maybe_vat_exempt' ) );
		add_action( 'wp_ajax_nopriv_euvi_maybe_vat_exempt', array( $this, 'maybe_vat_exempt' ) );

		add_filter( 'edd_payment_meta', array( $this, 'store_order_data' ) );
	}

	public function maybe_location_confirmation() {
		$this->reset();

		if ( ! $this->location_confirmation_required() ) {
			wp_die();
		}

		$taxed_country = $_POST['billing_country'];

		if ( ! $this->is_valid_eu_country( $taxed_country ) ) {
			wp_die();
		}

		$taxed_country = $this->get_country_by_code($taxed_country);
		echo $this->get_location_confirmation_checkbox(false, $taxed_country->name );
		wp_die();
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

	public function location_confirmation() {
		if ( $this->location_confirmation_required() ) {
			$location_confirmation_is_checked = isset( $_POST['euvi_location_confirmation'] );
			$customer_country = $this->get_country_by_code( $this->get_customer_country() )->name;

			echo $this->get_location_confirmation_checkbox( $location_confirmation_is_checked, $customer_country );
		}
	}

	private function get_location_confirmation_checkbox( $is_checked, $country ) {
		$output =  '<div id="euvi_location_confirmation">';
		$output .=  '<p class="form-row location_confirmation terms">';
		$output .= '<input type="checkbox" class="input-checkbox" name="euvi_location_confirmation"'. checked( $is_checked, true ) .' id="euvi_location_confirmation" />';
		$output .= '<label style="float: left;" for="euvi_location_confirmation" class="checkbox">I am established, have my permanent address, or usually reside in '. $country . '</label>';
		$output .= '</p>';
		$output .= '</div>';
		return $output;
	}

	private function location_confirmation_required() {
		if ( false === EDD()->session->get( 'euvi_vat_exempt' ) ) {
			$taxed_country = $this->get_customer_country();
			return ( $taxed_country !== $this->get_country_by_ip() );
		}

		return false;
	}

	private function get_customer_country() {
		if ( isset( $_POST['billing_country'])) {
			return $_POST['billing_country'];
		}

		if ( is_user_logged_in() ) {
			$user_address = get_user_meta( get_current_user_id(), '_edd_user_address', true );

			if ( isset( $user_address['country'] ) ) {
				return $user_address['country'];
			}
		}

		$settings = get_option('edd_settings');
		return $settings['base_country'];
	}

	private function get_country_by_ip() {
		$geolocate = new VIEU_Geolocate();
		return $geolocate->geolocate_ip();
	}

	public function validate_checkout( $valid_data, $data ) {
		$this->reset();

		if ( $this->location_confirmation_required() ) {
			if ( ! isset( $_POST['euvi_location_confirmation'] ) ) {
				edd_set_error( 'euvi-location-not-confirmed', 'Your IP Address does not match your billing country. Please confirm you are located within your billing country using the checkbox below.' );
			}
		}

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

		if ( $this->location_confirmation_required() ) {
			$payment_meta['location_confirmation_required'] = "yes";
		} else {
			$payment_meta['location_confirmation_required'] = "no";
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
		$settings = get_option('edd_settings');

		if ( $country_code == $settings['base_country'] ) {
			return false;
		}

		if ( ! $this->is_valid_eu_country( $country_code ) ) {
			return false;
		}

		$validator = new VIEU_VAT_Validator();
		return $validator->validate_vat($country_code, $vat_number);
	}

	private function get_country_by_code( $country_code ) {
		foreach ( $this->countries as $country ) {
			if ( $country->codes->alpha_2 == $country_code ) {
				return $country;
			}
		}
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