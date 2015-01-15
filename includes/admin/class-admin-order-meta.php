<?php

class EDD_VIEU_Admin_Order_Meta {
	public function __construct() {
		add_action( 'edd_payment_personal_details_list', array( $this, 'view_order_vat_number' ), 10, 2 );
		add_action( 'edd_payment_personal_details_list', array( $this, 'view_order_location_confirmation' ), 10, 2 );
	}

	/**
	 * Add the Vat Number to View Order Details
	 *
	 * @param Array   $payment_meta The payment meta associated with this order.
	 * @param Array   $user_info    The user information associated with this order.
	 * @return void
	 */
	public function view_order_vat_number( $payment_meta, $user_info ) {
		$vatnumber = isset( $payment_meta['vat_number'] ) ? $payment_meta['vat_number'] : '';
		?>
		<div class="column-container">
			<div class="column">
				<strong>VAT Number:</strong>&nbsp;
				<?php echo $vatnumber; ?>
			</div>
		</div>
	<?php
	}

	/**
	 * Add the Vat Number to View Order Details
	 *
	 * @param Array   $payment_meta The payment meta associated with this order.
	 * @param Array   $user_info    The user information associated with this order.
	 * @return void
	 */
	public function view_order_location_confirmation( $payment_meta, $user_info ) {
		$location_confirmed = isset( $payment_meta['location_confirmation_required'] ) ? $payment_meta['location_confirmation_required'] : 'No';
		?>
		<div class="column-container">
			<div class="column">
				<strong>Location confirmed:</strong>&nbsp;
				<?php echo $location_confirmed; ?>
			</div>
		</div>
	<?php
	}
}