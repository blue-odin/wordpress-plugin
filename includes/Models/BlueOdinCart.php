<?php

namespace BlueOdin\WordPress\Models;

use BlueOdin\WordPress\BlueOdinSession;
use WC_Cart;
use WC_Geolocation;

final class BlueOdinCart {

	/**
	 * @var int|null
	 */
	private $id;
	/**
	 * @var array<BlueOdinCartItem>
	 */
	private $items = [];
	/**
	 * @var string
	 */
	private $status = 'in-process';
	/**
	 * @var WC_Cart|null
	 */
	private $wc_cart;
	/**
	 * @var BlueOdinSession
	 */
	private $session;

	/**
	 * @param BlueOdinSession $session
	 */
	public function __construct( BlueOdinSession $session )
	{
		$this->session = $session;
	}

	/**
	 * @param WC_Cart $wc_cart
	 * @param BlueOdinSession $session
	 *
	 * @return BlueOdinCart
	 */
	public static function fromWC_Cart( WC_Cart $wc_cart, BlueOdinSession $session ): BlueOdinCart
	{
		$cart          = new BlueOdinCart( $session );
		$cart->wc_cart = $wc_cart;
		foreach ( $wc_cart->cart_contents as $wc_item ) {
			$cart->addItem( BlueOdinCartItem::fromWC_Cart( $wc_item ) );
		}

		return $cart;
	}

	public static function fromAddedItem( BlueOdinSession $session, string $key, int $product_id, int $quantity ): BlueOdinCart
	{
		$cart = new BlueOdinCart( $session );
		$cart->addItem( new BlueOdinCartItem( $key, $product_id, $quantity ) );
		return $cart;
	}


	public function save_to_db(): void
	{
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
		$wpdb->query( $query );

		$wpdb->delete( $wpdb->prefix . 'bo_cart_items', [ 'cart_id' => $this->cart_id() ] );
		foreach ( $this->items as $item ) {
			$item->save_to_database();
		}
	}

	/**
	 * @return int
	 */
	public function cart_id(): int
	{
		if ( $this->id ) {
			return $this->id;
		}

		global $wpdb;
		$query    = $wpdb->prepare(
			"SELECT id FROM {$wpdb->prefix}bo_carts WHERE session_id=%s ",
			$this->session->get_session_id()
		);
		$this->id = $wpdb->get_var( $query );

		return $this->id;
	}

	/**
	 * @return WC_Cart
	 */
	public function wc_cart(): WC_Cart
	{
		return $this->wc_cart;
	}

	/**
	 * @return void
	 */
	public function push_to_blueodin(): void
	{
		do_action( 'blueodin_cart_updated', [
			'id'     => $this->cart_id(),
			'data'   => $this->toArray(),
			'action' => 'updated'
		] );
	}

	private function toArray(): array
	{
		$items = [];
		foreach ( $this->items as $item ) {
			$items[] = $item->toArray();
		}

		return [
			'id'               => $this->id,
			'session_id'       => $this->session->get_session_id(),
			'email_address'    => 'unknown',
			'customer_details' => 'guest',
			'order_total'      => 12.34,
			'coupons'          => [],
			'captured_by'      => 'unknown',
			'cart_status'      => $this->status,
			'items'            => $items,
		];
	}

	public function addItem( BlueOdinCartItem $item ): void
	{
		$item->setCart( $this );
		$this->items[] = $item;
	}
}