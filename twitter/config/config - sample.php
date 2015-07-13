<?php

// TODO: Simplfy
Class Config {
	private $consumer_key;
	private $consumer_secret;
	private $oauth_access_token;
	private $oauth_access_token_secret;

	public function __construct() {
		$this->consumer_key = "";
		$this->consumer_secret = "";
		$this->oauth_access_token = "";
		$this->oauth_access_token_secret = "";
	}

	public function getSettings() {
		$settings = array(
			"consumer_key" => $this->consumer_key,
			"consumer_secret" => $this->consumer_secret,
			"oauth_access_token" => $this->oauth_access_token,
			"oauth_access_token_secret" => $this->oauth_access_token_secret
		);

		return $settings;
	}
}