<?php

require_once "twitter/includes/Tweet.php";
require_once "twitter/includes/TweetCollection.php";

// Require the OAuth class
require_once('twitter/includes/twitter-api-oauth.php');

/**
 * twitter-timeline-php : Twitter API 1.1 user timeline implemented with PHP, a little JavaScript, and web intents
 *
 * @package		twitter-timeline-php
 * @author		Kim Maida <contact@kim-maida.com>, Mike Behnke <mike@local-pc-guy.com>
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link		http://github.com/kmaida/twitter-timeline-php
 * @credits		Thank you to <http://viralpatel.net/blogs/twitter-like-n-min-sec-ago-timestamp-in-php-mysql/> for base for "time ago" calculations
 *
**/

class TwitterTimeline
{
	public $tweets;
	public $twitterUsername;
	private $twitter_data;
	private $json;

	public function __construct($username, $tweetlimit)
	{
		$this->twitterUsername = $username;
		$tweetCount = $tweetlimit;

		###############################################################
		## SETTINGS

		// Set access tokens <https://dev.twitter.com/apps/>
		$settings = array(
			'consumer_key' => "",
			'consumer_secret' => "",
			'oauth_access_token' => "",
			'oauth_access_token_secret' => ""
		);

		// get settings information from file, do not source control
		$configFile = '_utils/config.php';
		if (is_file($configFile)) {
			include $configFile;
			$config = new Config();
			$settings = $config->getSettings();
		}

		// print_r($settings);

		// Set API request URL and timeline variables if needed <https://dev.twitter.com/docs/api/1.1>
		$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
		//$url = 'https://api.twitter.com/1.1/search/tweets.json';

		###############################################################
		## MAKE GET REQUEST

		$getfield = '?screen_name=' . $this->twitterUsername . '&count=' . $tweetCount;

		$twitter = new TwitterAPITimeline($settings);

		// Note: Set the GET field BEFORE calling buildOauth()
		$this->json = $twitter->setGetfield($getfield) 
							  ->buildOauth($url)
							  ->performRequest();

		// Create an array with the fetched JSON data
		$this->twitter_data = json_decode($this->json, true);    

		// Uncomment this line to view the entire JSON array. Helpful: http://www.freeformatter.com/json-formatter.html
		//echo $this->json;
		//echo $this->twitter_data;

		###############################################################
		## PREP DATA IN $tweets
		if (!is_null($this->twitter_data)) {
			$this->tweets = new TweetCollection($this->twitter_data);
		} else {
			$this->twitter_data = 'no twitter data';
		}
	}
}