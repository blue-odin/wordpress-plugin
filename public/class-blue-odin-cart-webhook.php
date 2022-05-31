<?php

class BlueOdinCartWebhook {
	/**
	 * @param BlueOdinLoader $loader
	 */
	public function __construct($loader) {
		$loader->add_filter( 'woocommerce_webhook_topic_hooks', $this, 'filter_woocommerce_webhook_topic_hooks' );
		$loader->add_filter( 'woocommerce_valid_webhook_resources', $this, 'filter_woocommerce_valid_webhook_resources' );
		$loader->add_filter( 'woocommerce_webhook_topics', $this, 'filter_woocommerce_webhook_topics' );
		$loader->add_filter( 'woocommerce_webhook_payload', $this, 'filter_woocommerce_webhook_payload' , 10, 4 );

		add_filter( 'http_request_args', function( $args ) {
			$args['reject_unsafe_urls'] = false;

			return $args;
		});

	}

	/**
	 *  add a new webhook topic hook.
	 * @param array $topic_hooks Existing topic hooks.
	 */
	function filter_woocommerce_webhook_topic_hooks( $topic_hooks ) {
		//blueodin_write_log("filter_woocommerce_webhook_topic_hooks", ['topic_hooks' => $topic_hooks]);
		// Array that has the topic as resource.event with arrays of actions that call that topic.
		$new_hooks = [
			'bo_cart.updated' => [
				'blueodin_cart_updated',
			],
		];

		return array_merge( $topic_hooks, $new_hooks );
	}



	/**
	 * add new resources for carts.
	 * @param array $topic_resources Existing valid resources.
	 */
	function filter_woocommerce_valid_webhook_resources( $topic_resources) {
		//blueodin_write_log("filter_woocommerce_valid_webhook_resources", ['topic_events' => $topic_resources]);

		$topic_resources[] = 'bo_cart';

		return $topic_resources;
	}



	/**
	 * add_new_webhook_topics adds the new webhook to the dropdown list on the Webhook page.
	 * @param array $topics Array of topics with the i18n proper name.
	 */
	function filter_woocommerce_webhook_topics( $topics ) {
		//blueodin_write_log('filter_woocommerce_webhook_topics', ['topics' => $topics]);
		// New topic array to add to the list, must match hooks being created.
		$new_topics = [
			'bo_cart.updated' => __( 'Cart Updated', 'woocommerce' ),
		];

		return array_merge( $topics, $new_topics );
	}

	    /**
     * Generate data for webhook delivery.
     *
     * @param array  $payload - Array of Data.
     * @param string $resource - Resource.
     * @param array  $resource_data - Resource Data.
     * @param int    $id - Webhook ID.
     * @return array $payload - Array of Data.
     *
     * @since 8.7.0
     */
    public function filter_woocommerce_webhook_payload( $payload, $resource, $resource_data, $id ) {
	    blueodin_write_log('filter_woocommerce_webhook_payload', ['payload' => $payload, 'resource' => $resource, 'resource_data' => $resource_data, 'id' => $id]);

		if ($resource !== 'bo_cart') {
			return $payload;
		}

        switch ( $resource_data['action'] ) {
            case 'updated':
                $webhook_meta = array(
                    'webhook_id'          => $id,
                    'webhook_action'      => $resource_data['action'],
                    'webhook_resource'    => $resource,
                    'webhook_resource_id' => $resource_data['id'],
                );

                $payload = array_merge( $webhook_meta, $resource_data['data'] );
                break;
        }

        return $payload;
    }


}