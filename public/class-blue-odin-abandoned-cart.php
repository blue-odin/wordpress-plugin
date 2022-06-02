<?php

final class BlueOdinAbandonedCart {

	/**
	 * @var BlueOdinSession
	 */
	private $session;

	/**
	 * @param BlueOdinSession $session
	 */
	public function __construct( BlueOdinSession $session) {
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
	public function action_woocommerce_add_to_cart(
		string $cart_item_key,
		int $product_id,
		string $quantity,
		int $variation_id,
		array $variation,
		array $cart_item_data ): void
	{
		blueodin_write_log("in action_woocommerce_add_to_cart function", [
			'cart_item_key' => $cart_item_key,
			'product_id' => $product_id,
			'quantity' => $quantity,
			'variation_id' => $variation_id,
			'variation' => $variation,
			'cart_item_data' => $cart_item_data,
		]);

		$cart = BlueOdinCart::fromAddedItem($this->session, $cart_item_key, $product_id, $quantity );

		$cart->save_to_db();
		$cart->push_to_blueodin();
	}

	/**
	 * @param string $cart_item_key
	 * @param WC_Cart $wc_cart
	 *
	 * @return void
	 */
	public function action_woocommerce_cart_item_removed( string $cart_item_key, WC_Cart $wc_cart ): void
	{
		blueodin_write_log("in action_woocommerce_cart_item_removed function", [
			'cart_item_key' => $cart_item_key,
			'cart' => $wc_cart,
		]);
		$cart = BlueOdinCart::fromWC_Cart( $wc_cart, $this->session );

		$cart->save_to_db();
		$cart->push_to_blueodin();

	}

	/**
	 * @param string $cart_item_key
	 * @param WC_Cart $wc_cart
	 *
	 * @return void
	 */
	public function action_woocommerce_cart_item_restored( string $cart_item_key, WC_Cart $wc_cart ): void
	{
		blueodin_write_log("in action_woocommerce_cart_item_restored function", [
			'cart_item_key' => $cart_item_key,
			'cart' => $wc_cart,
		]);
		$cart = BlueOdinCart::fromWC_Cart( $wc_cart, $this->session );

		$cart->save_to_db();
		$cart->push_to_blueodin();
	}

	/**
	 * @param bool $clear_persistent_cart
	 *
	 * @return void
	 */
	public function action_woocommerce_cart_emptied( bool $clear_persistent_cart ): void
	{
		blueodin_write_log("in action_woocommerce_cart_emptied function", [
			'clear_persistent_cart' => $clear_persistent_cart,
		]);
	}

	/**
	 * @param string $cart_item_key
	 * @param int $quantity
	 * @param WC_Cart $wc_cart
	 *
	 * @return void
	 */
	public function action_woocommerce_cart_item_set_quantity( string $cart_item_key, int $quantity, WC_Cart $wc_cart ): void
	{
		blueodin_write_log("in action_woocommerce_cart_item_set_quantity function", [
			'cart_item_key' => $cart_item_key,
			'quantity' => $quantity,
			'cart' => $wc_cart,
		]);
		$cart = BlueOdinCart::fromWC_Cart( $wc_cart, $this->session );

		$cart->save_to_db();
		$cart->push_to_blueodin();

	}

}