<?php

final class BlueOdinAbandonedCart {

	/**
	 * @var BlueOdinSession
	 */
	private $session;

	/**
	 * @param BlueOdinSession $session
	 */
	public function __construct($session) {
		$this->session = $session;
	}


	/**
	 * @param string $cart_item_key
	 * @param int $product_id
	 * @param string $quantity
	 * @param int $variation_id
	 * @param array $variation
	 * @param array $cart_item_data
	 *
	 * @return void
	 */
	public function action_woocommerce_add_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		blueodin_write_log("in action_woocommerce_add_to_cart function", [
			'cart_item_key' => $cart_item_key,
			'product_id' => $product_id,
			'quantity' => $quantity,
			'variation_id' => $variation_id,
			'variation' => $variation,
			'cart_item_data' => $cart_item_data,
		]);
	}

	/**
	 * @param string $cart_item_key
	 * @param WC_Cart $cart
	 *
	 * @return void
	 */
	public function action_woocommerce_cart_item_removed( $cart_item_key, $cart ) {
		blueodin_write_log("in action_woocommerce_cart_item_removed function", [
			'cart_item_key' => $cart_item_key,
			'cart' => $cart,
		]);
		$cart_id = $this->save_cart_to_database($cart);
		do_action( 'blueodin_cart_updated', ['id' => $cart_id, 'data' => $cart, 'action' => 'updated']);
	}

	/**
	 * @param string $cart_item_key
	 * @param WC_Cart $cart
	 *
	 * @return void
	 */
	public function action_woocommerce_cart_item_restored($cart_item_key, $cart ) {
		blueodin_write_log("in action_woocommerce_cart_item_restored function", [
			'cart_item_key' => $cart_item_key,
			'cart' => $cart,
		]);
		$cart_id = $this->save_cart_to_database($cart);
		do_action( 'blueodin_cart_updated', ['id' => $cart_id, 'data' => $cart, 'action' => 'updated']);
	}

	/**
	 * @param bool $clear_persistent_cart
	 *
	 * @return void
	 */
	public function action_woocommerce_cart_emptied( $clear_persistent_cart ) {
		blueodin_write_log("in action_woocommerce_cart_emptied function", [
			'clear_persistent_cart' => $clear_persistent_cart,
		]);
	}

	/**
	 * @param string $cart_item_key
	 * @param int $quantity
	 * @param WC_Cart $cart
	 *
	 * @return void
	 */
	public function action_woocommerce_cart_item_set_quantity($cart_item_key, $quantity, $cart ) {
		blueodin_write_log("in action_woocommerce_cart_item_set_quantity function", [
			'cart_item_key' => $cart_item_key,
			'quantity' => $quantity,
			'cart' => $cart,
		]);
		$cart_id = $this->save_cart_to_database($cart);
		do_action( 'blueodin_cart_updated', ['id' => $cart_id, 'data' => $cart, 'action' => 'updated']);
	}

	private function save_cart_to_database($cart) {

		global $wpdb;
		$user_id    = get_current_user_id();
		$ip_address = WC_Geolocation::get_ip_address();
		$session_id = $this->session->get_session_id();
		$query      = $wpdb->prepare(
			"INSERT INTO {$wpdb->prefix}bo_carts(time, session_id, user_id, ip_address ) VALUES (now(), %s, %s, %s ) ON DUPLICATE KEY UPDATE user_id=%s, ip_address = %s, time = now()",
			$session_id,
			$user_id,
			$ip_address,
			$user_id,
			$ip_address
		);
		//blueodin_write_log('save_cart_to_database', $query);
		$wpdb->query($query);

		$cart_id = $this->get_cart_id();

		foreach($cart->cart_contents as $key => $item) {
			$this->save_cart_item_to_database($cart_id, $item);
		}
		return $cart_id;
	}

	/**
	 * @return int
	 */
	private function get_cart_id() {
		global $wpdb;
		$query = $wpdb->prepare(
			"SELECT id FROM {$wpdb->prefix}bo_carts WHERE session_id=%s ",
			$this->session->get_session_id()
		);
		return $wpdb->get_var($query);
	}

	private function save_cart_item_to_database( $cart_id, $item ) {
		global $wpdb;

		$query      = $wpdb->prepare(
			"INSERT INTO {$wpdb->prefix}bo_cart_items(cart_id, item_key, product_id, quantity) VALUES (%d, %s, %d, %d ) ON DUPLICATE KEY UPDATE product_id=%d, quantity = %d",
			$cart_id,
			$item['key'],
			$item['product_id'],
			$item['quantity'],
			$item['product_id'],
			$item['quantity']
		);
		//blueodin_write_log('save_cart_item_to_database', ['query' => $query, 'item' => $item]);
		$wpdb->query($query);
	}

}