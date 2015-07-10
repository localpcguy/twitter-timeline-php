<?php

Class Tweet {
	// User details
	public $userHandle;					// Twitter handle
	public $userScreenName;				// Twitter screenname
	public $userAccountURL;				// Twitter url
	public $userAvatarURL;				// Twitter avatar url

	// Retweeted User details
	public $retweetingUser;				// Twitter handle for retweeted user
	public $retweetingUserScreenName;	// Twitter screenname for retweeted user

	// Tweet details
	public $id; 						// Tweet id
	private $_tweetBody;				// Tweet body (raw)
	public $formattedTweet;				// Tweet body (formatted, linkified)
	private $_tweetTimestamp;			// Tweet timestamp (raw)
	public $tweetDisplayTime;			// Tweet timestamp (timeago style)
	public $statusURL;					// Tweet url
	public $replyURL;					// Tweet reply url
	public $retweetURL;					// Tweet retweet url
	public $retweeted;					// Did the viewing user retweet this tweet
	public $favoriteURL;				// Tweet favorite url
	public $favorited;					// Did the viewing user favorite this tweet

	// Retweeted?
	private $_retweet;					// Retweet data
	public $isRetweet;					// isRetweet

	// Reply?
	private $_replyID;					// Reply ID
	public $isReply;					// isReply


	public function __construct($tweet) {
		//print_r($tweet);
		if (!is_null($tweet)) {
			# Retweet?
			$this->_retweet = $tweet['retweeted_status'];
			$this->isRetweet = !empty($this->retweet); 
			
			# Reply?
			$this->originalUserId = $tweet['in_reply_to_status_id'];
			$this->originalUser = $tweet['in_reply_to_screen_name'];
			$this->isReply = !empty($this->replyID);

			# The user
			# Tweet source user (could be a retweeted user and not the owner of the timeline)	
			$user = $this->isRetweet ? $this->retweet['user'] : $tweet['user'];
			$this->userHandle = $user['user']['name'];
			$this->userScreenName = $user['user']['screen_name'];
			$this->userAvatarURL = stripcslashes($user['user']['profile_image_url']);
			$this->userAccountURL = 'http://twitter.com/' . $this->userScreenName;

			# Retweet - get the retweeter's name and screen name
			$this->retweetingUser = $this->isRetweet ? $tweet['user']['name'] : null;
			$this->retweetingUserScreenName = $this->isRetweet ? $tweet['user']['screen_name'] : null;

			# The tweet
			$this->id = $tweet['id_str'];
			$this->_tweetBody = $tweet['text'];
			$this->statusURL = 'http://twitter.com/' . $this->userScreenName . '/status/' . $this->id;
			$this->_tweetTimestamp = $tweet['created_at'];
			$this->formatTweet();
			$this->timeAgo();

			# Tweet actions (uses web intents)
			$this->replyURL = 'https://twitter.com/intent/tweet?in_reply_to=' . $this->id;
			$this->retweetURL = 'https://twitter.com/intent/retweet?tweet_id=' . $this->id;
			$this->retweeted = $tweet['retweeted'];
			$this->favoriteURL = 'https://twitter.com/intent/favorite?tweet_id=' . $this->id;
			$this->favorited = $tweet['favorited'];
		}
	}

	public function getTweetHtml($twElement = "div", $twClass = "tweet") {

		$tweetClasses = '';
		if ($this->retweeted) $tweetClasses .= ' visitor-retweeted';
		if ($this->favorited) $tweetClasses .= ' visitor-favorited';
		if ($this->isRetweet) $tweetClasses .= ' is-retweet';
		if ($this->isReply) $tweetClasses .= ' is-reply';

		$retweetAndReplyHtml = '';
		if ($this->isRetweet) {
			$retweetAndReplyHtml .= '<span class="retweeter">Retweeted by <a class="link-retweeter" href="http://twitter.com/' . $this->retweetingUserScreenName . '">' . $this->retweetingUser . '</a></span>';
		}
		
		if ($this->isReply) {
			$retweetAndReplyHtml .= '<a class="link-reply-to permalink-status" href="http://twitter.com/' . $this->originalUser . '/status/' . $this->originalUserId . '">In reply to...</a>';
		}
		
		$baseHtml =  <<<TWT
			<$twElement id="tweetid-$this->id" class="$twClass $tweetClasses">
				<div class="tweet-info">
					<div class="user-info">
						<a class="user-avatar-link" href="$this->userAccountURL">
							<img class="user-avatar" src="$this->userAvatarURL">
						</a>
						<p class="user-account">
							<a class="user-name" href="$this->userAccountURL"><strong>$this->userHandle</strong></a>
							<a class="user-screenName" href="$this->userAccountURL">@$this->userScreenName</a>
						</p>
					</div>
					<a class="tweet-date permalink-status" href="$this->statusURL" target="_blank">
						$this->tweetDisplayTime
					</a>
				</div>
				<blockquote class="tweet-text">
					<p>$this->formattedTweet</p> 
					<p class="tweet-details">
						<a class="link-details permalink-status" href="$this->statusURL" target="_blank">Details</a>
						$retweetAndReplyHtml
					</p>
				</blockquote>
				<div class="tweet-actions">
					<a class="action-reply" href="$this->replyURL">Reply</a>
					<a class="action-retweet" href="$this->retweetURL">Retweet</a>
					<a class="action-favorite" href="$this->favoriteURL">Favorite</a>
				</div>
			</$twElement>
TWT;
		return $baseHtml;
	}

	private function formatTweet() {
		$linkified = '@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@';
		$hashified = '/(^|[\n\s])#([^\s"\t\n\r<:]*)/is';
		$mentionified = '/(^|[\n\s])@([^\s"\t\n\r<:]*)/is';

		$prettyTweet = preg_replace(
			array(
				$linkified,
				$hashified,
				$mentionified
			),
			array(
				'<a href="$1" class="link-tweet" target="_blank">$1</a>',
				'$1<a class="link-hashtag" href="https://twitter.com/search?q=%23$2&src=hash" target="_blank">#$2</a>',
				'$1<a class="link-mention" href="http://twitter.com/$2" target="_blank">@$2</a>'
			),
			$this->_tweetBody
		);

		$this->formattedTweet = $prettyTweet;
	}

	private function timeAgo() {
		$timestamp = strtotime($this->_tweetTimestamp);
		$day = 60 * 60 * 24;
		$today = time(); // current unix time
		$since = $today - $timestamp;

		# If it's been less than 1 day since the tweet was posted, figure out how long ago in seconds/minutes/hours
		if (($since / $day) < 1) {

			$timeUnits = array(
				array(60 * 60, 'h'),
				array(60, 'm'),
				array(1, 's')
			);

			for ($i = 0, $n = count($timeUnits); $i < $n; $i++) {
				$seconds = $timeUnits[$i][0];
				$unit = $timeUnits[$i][1];

				if (($count = floor($since / $seconds)) != 0) {
					break;
				}
			}

			$this->tweetDisplayTime = "$count{$unit}";

			# If it's been a day or more, return the date: day (without leading 0) and 3-letter month
		} else {
			$this->tweetDisplayTime = date('j M', strtotime($this->_tweetTimestamp));
		}
	}
}

/*

<script id="tweetTpl" type="text/html">
	<div class="tweet ">
		<i class="sprite twitterBirdIcon desktopOnly"></i>
		<a target="_blank" href="{{userAccountURL}}"><img class="twitter-avatar" src="{{userAvatarURL}}" alt="Twitter User Avatar" /></a>
		<div class="twitter-body">
			<h2 class="twitter-heading-wrap"><a class="twitter-heading" href="{{userAccountURL}}" target="_blank">{{userHandle}}</a></h2>
			<a target="_blank" class="twitter-userhandler" href="{{userAccountURL}}">@{{userScreenName}}</a>
		</div>
		<p class="twitter-copy">{{formattedTweet}}</p>
		<div class="tweet-actions">
			<a class="twitter-subheading" target="_blank" href="{{statusURL}}">{{tweetDisplayTime}}</a>
			<div class="tweet-actions-body">
				<a href="{{replyURL}}" class="action-reply"><i class="sprite replyIcon"></i> Reply</a>
				<a href="{{retweetURL}}" class="action-retweet {{retweeted}}"><i class="sprite retweetIcon"></i> Retweet</a>
				<a href="{{favoriteURL}}" class="action-favorite {{favorited}}"><i class="sprite favoriteIcon"></i> Favorite</a>
			</div>
		</div>
	</div>
</script>


 */