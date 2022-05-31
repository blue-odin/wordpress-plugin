<?php

final class BlueOdinSession {

	const SESSION_ID_COOKIE_NAME = 'wordpress_bo_session';
	/**
	 * @var mixed|string|null
	 */
	private $session_id;

	/**
	 * @return string
	 */
	public function get_session_id() {

		if(!is_null($this->session_id)) {
			return $this->session_id;
		}


		if(isset($_COOKIE[ self::SESSION_ID_COOKIE_NAME ])) {
			return $this->load_session_from_cookie();
		}

		return $this->create_new_session();
	}

	/**
	 * @return mixed|string|null
	 */
	private function create_new_session() {
		$domain = parse_url( home_url() )['host'];

		$this->session_id = wp_generate_uuid4();
		setcookie( self::SESSION_ID_COOKIE_NAME, $this->session_id, time() + 31556926, '/', $domain );

		return $this->session_id;
	}

	/**
	 * @return mixed|string|null
	 */
	// TODO: validate is a uuid
	private function load_session_from_cookie() {
		$this->session_id = $_COOKIE[ self::SESSION_ID_COOKIE_NAME ];

		return $this->session_id;
	}
}