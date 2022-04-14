<?php

final class BlueOdinUTMTracking {

	const SESSION_ID_COOKIE_NAME = 'bo_session';
	const BO_THANKYOU_ACTION_DONE = '_bo_thankyou_action_done';
	private $parameter_names = [
		'utm_campaign',
		'utm_source',
		'utm_medium',
		'utm_content',
		'utm_id',
		'utm_term',
	];

	/**
	 * @var array<string,string> $parameters
	 */
	private $parameters = [];
	/**
	 * @var string $session_id
	 */
	private $session_id;

	public function __construct() {

	}

	/**
	 * @param array $vars
	 *
	 * @return array
	 */
	public function filter_query_vars( $vars ){
		//error_log("in query_vars function");
		foreach ($this->parameter_names as $parameter) {
			$vars[] = $parameter;
		}

		return $vars;
	}

	function action_init() {
		if (is_404()) {
			return;
		}

		$this->get_session_id();
	}


	/**
	 * @param WP $wp
	 *
	 * @return void
	 */
	public function action_wp($wp) {
		if (is_404()) {
			return;
		}

		//error_log("url is: " . home_url(add_query_arg($wp->query_vars,  $wp->request )));

		global $wp_query;
		foreach ($this->parameter_names as $name) {
			$value = $wp_query->get( $name, null );
			if ($value) {
				$this->parameters[ $name ] = $value;
			}
		}
		//error_log($this->session_id);

		$this->save_parameters();
	}

	function action_woocommerce_thankyou( $order_id ) {
		//error_log("in action_woocommerce_thankyou");
		if ( ! $order_id ) {
			return;
		}

		// Allow code execution only once
		if( get_post_meta( $order_id, self::BO_THANKYOU_ACTION_DONE, true ) ) {
			return;
		}

		$this->load_parameters();

		// Get an instance of the WC_Order object
		$order = wc_get_order( $order_id );

		foreach($this->parameters as $name => $value) {
				$order->update_meta_data("_bo_" . $name, $value );
		}

		// Flag the action as done (to avoid repetitions on reload for example)
		$order->update_meta_data( self::BO_THANKYOU_ACTION_DONE, true );
		$order->save();
	}


	private function get_session_id() {

		// TODO: validate is a uuid
		if(isset($_COOKIE[ self::SESSION_ID_COOKIE_NAME ])) {
			$this->session_id = $_COOKIE[ self::SESSION_ID_COOKIE_NAME ];
			return;
		}

		$domain = parse_url(home_url())['host'];

		$this->session_id = wp_generate_uuid4();
		setcookie( self::SESSION_ID_COOKIE_NAME, $this->session_id, time() + 31556926,  '/', $domain);
	}

	private function save_parameters() {
		//error_log("save_parameters " . print_r($this->parameters, true));

		global $wpdb;

		foreach ($this->parameters as $name => $value) {

			$query = $wpdb->prepare(
				"INSERT INTO {$wpdb->prefix}bo_utm_data(time, session_id, name, value ) VALUES (now(), %s, %s, %s ) ON DUPLICATE KEY UPDATE value=%s",
				$this->session_id,
				$name,
				$value,
				$value
			);
			//error_log($query);
			$wpdb->query($query);
		}
	}

	private function load_parameters() {
		//error_log("load_parameters " . print_r($this->parameters, true));

		global $wpdb;

		$query = $wpdb->prepare(
			"SELECT name, value FROM {$wpdb->prefix}bo_utm_data WHERE session_id=%s ",
			$this->session_id
		);
		$results = $wpdb->get_results($query);
		foreach ($results as $result) {
			$this->parameters[$result->name] = $result->value;
		}
	}
}