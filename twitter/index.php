<?php

require_once "includes/RestServer.php";
require_once "includes/TwitterTimeline.php";

class Twitter {
	// example: twitter/index.php?method=getTweets&username=twitteruser&limit=4
	public function getTweets($username, $limit) {
		$twitterTimeline = new TwitterTimeline($username, 200);
		$returnedTweets = array();
		for ($i = 0; $i < $limit; $i++) {
			array_push($returnedTweets, $twitterTimeline->tweets[$i]);
		}
		return $returnedTweets;
	}

	// example: twitter/index.php?method=getTweetsWithHashtag&username=twitteruser&hashtag=thehashtag&limit=4
	public function getTweetsWithHashtag($username, $hashtag, $limit) {
		// filter tweets by hashtag/limit
		$twitterTimeline = new TwitterTimeline($username, 200);
		$returnedTweets = array();
		for ($i = 0; $i < count($twitterTimeline->tweets) && count($returnedTweets) < $limit; $i++) {
			if (strpos(strtolower($twitterTimeline->tweets[$i]->formattedTweet), strtolower($hashtag)) !== false) {
				array_push($returnedTweets, $twitterTimeline->tweets[$i]);
			}
		}
		return $returnedTweets;
	}
}

header('Content-Type: application/json');
$rest = new RestServer();
$rest->addServiceClass(new Twitter());
$rest->handle();

?>
