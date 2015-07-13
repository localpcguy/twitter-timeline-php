<?php

Class TweetCollection {
	public $tweets;
	private $tweetsHtml;
	private $tweetsHtmlUpdated;

	public function __construct($twitterData) {
		 $this->_setTweets($twitterData);
	}

	private function _setTweets($twitterData) {
		$this->tweets = array();
		$i = 0;

		foreach ($twitterData as $tweet) {
			array_push($this->tweets, new Tweet($tweet));
			++$i;
		}
		
		$this->tweetsHtmlUpdated = false;
	}

	public function updateTweets($twitterData) {
		 $this->_setTweets($twitterData);
	}

	public function getTweetsHtml($wrapTag = 'div', $wrapClass = 'tweet-list') {
		$tweetsHtmlFormat = '<%s class="%s">%s</%s>';
		$tweetsHtml = '';

		if (strlen($this->tweetsHtml) > strlen($tweetsHtmlFormat) && $this->tweetsHtmlUpdated) {
			return $this->tweetsHtml;
		}

		foreach ($this->tweets as $tweet) {
			$tweetsHtml .= $tweet->getTweetHtml();
		}
		$this->tweetsHtml = sprintf($tweetsHtmlFormat, $wrapTag, $wrapClass, $tweetsHtml, $wrapTag);

		$this->tweetsHtmlUpdated = true;

		return $this->tweetsHtml;
	}
}