<?php

namespace BlueOdin\WordPress\Admin;

use BlueOdin\WordPress\BlueOdin_i18n;
use BlueOdin\WordPress\BlueOdinLoader;
use WC_Order;

final class CostOfGoods {

	const META_KEY = '_wc_cog_cost';
	const WC_COG_ITEM_COST = '_wc_cog_item_cost';
	const WC_COG_ITEM_TOTAL_COST = '_wc_cog_item_total_cost';
	const WC_COG_ORDER_TOTAL_COST = '_wc_cog_order_total_cost';

	public function __construct()
	{

	}

	public static function load( BlueOdinLoader $loader ): self
	{
		$cost_of_goods = new CostOfGoods();
		$loader->add_action( 'woocommerce_product_options_pricing', $cost_of_goods, 'action_woocommerce_product_options_pricing' );
		$loader->add_action( 'woocommerce_process_product_meta', $cost_of_goods, 'action_woocommerce_process_product_meta', 10, 2 );
		$loader->add_action( 'woocommerce_checkout_update_order_meta', $cost_of_goods, 'action_woocommerce_checkout_update_order_meta', 10, 1 );
		$loader->add_filter( 'woocommerce_hidden_order_itemmeta', $cost_of_goods, 'filter_woocommerce_hidden_order_itemmeta' );

		return $cost_of_goods;
	}

	public function action_woocommerce_product_options_pricing()
	{

		woocommerce_wp_text_input( [
			'id'          => self::META_KEY,
			'class'       => 'wc_input_price short',
			'label'       => sprintf( __( 'Cost of Good (%s)', BlueOdin_i18n::TEXTDOMAIN ), '<span>' . get_woocommerce_currency_symbol() . '</span>' ),
			'data_type'   => 'price',
			'desc_tip'    => true,
			'description' => __( 'Cost of Goods is the cost of the product, excluding any additional costs such as shipping, taxes, etc.', BlueOdin_i18n::TEXTDOMAIN ),
		] );
	}

	/**
	 * Save cost field for simple product
	 *
	 * @param int $post_id post id
	 *
	 */
	public function action_woocommerce_process_product_meta( int $post_id )
	{
		update_post_meta( $post_id, self::META_KEY, stripslashes( wc_format_decimal( $_POST[ self::META_KEY ] ) ) );
	}

	/**
	 * Sets the cost of goods for a given order.
	 *
	 * In WC 3.0+ this simply sums up all the line item total costs.
	 *
	 * @param int|\WP_Post|WC_Order $order_id the order ID, post object, or order object
	 *
	 */
	public function action_woocommerce_checkout_update_order_meta( $order_id )
	{

		$order = wc_get_order( $order_id );

		$total_cost = 0;

		foreach ( $order->get_items() as $item_id => $item ) {

			if ( ! $item_id || empty( $item ) ) {
				continue;
			}

			$product_id = ( ! empty( $item['variation_id'] ) ) ? $item['variation_id'] : $item['product_id'];
			$item_cost  = $this->get_cost( $product_id );
			$quantity   = $item->get_quantity();

			$this->set_item_cost_meta( $item_id, $item_cost, $quantity );

			// add to the item cost to the total order cost.
			$total_cost += ( $item_cost * $quantity );

		}

		$formatted_total_cost = wc_format_decimal( $total_cost, wc_get_price_decimals() );

		$order->update_meta_data( self::WC_COG_ORDER_TOTAL_COST, $formatted_total_cost );
		$order->save_meta_data();
	}

	/**
	 * Returns the product cost, if any
	 *
	 * @param int $product_id product id
	 *
	 * @return float product cost if configured, the empty string otherwise
	 * @since 1.1
	 */
	public function get_cost( int $product_id ): float
	{
		$product = wc_get_product( $product_id );

		if ( ! $product instanceof \WC_Product ) {
			return 0.0;
		}

		return $product->get_meta( self::META_KEY, true, 'edit' );
	}

	/**
	 * Sets an order item's cost meta.
	 *
	 * @param int $item_id item ID
	 * @param float|string $item_cost item cost
	 * @param int $quantity item quantity
	 *
	 */
	private function set_item_cost_meta( int $item_id, float $item_cost, int $quantity ): void
	{

		if ( empty( $item_cost ) || ! is_numeric( $item_cost ) ) {
			$item_cost = '0';
		}

		$formatted_cost  = wc_format_decimal( $item_cost );
		$formatted_total = wc_format_decimal( $item_cost * $quantity );

		try {
			wc_update_order_item_meta( $item_id, self::WC_COG_ITEM_COST, $formatted_cost );
			wc_update_order_item_meta( $item_id, self::WC_COG_ITEM_TOTAL_COST, $formatted_total );
		} catch ( \Exception $e ) {
		}
	}

	public function filter_woocommerce_hidden_order_itemmeta( array $hidden_fields ): array
	{
		return array_merge( $hidden_fields, [ self::WC_COG_ITEM_COST, self::WC_COG_ITEM_TOTAL_COST ] );
	}

}