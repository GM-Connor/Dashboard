<?php

define('REFRESH_TIME', 60*60*3); // 3 hours

/**
 * Class to handle twitter
 */

class Twitter {

	/**
	* Sets the object's properties using the values in the supplied array
	*
	* @param assoc The property values
	*/

	public function __construct( $Settings ) {

		$this->data_dir = "data";
		$this->twitter_dir = $this->data_dir . "/twitter";
		$this->friends_file = $this->twitter_dir . "/friends.json";

		$this->creds = array(
			'oauth_access_token' => $Settings->getSettingValue('T:OAuth access token')[0],
			'oauth_access_token_secret' => $Settings->getSettingValue('T:OAuth access token secret')[0],
			'consumer_key' => $Settings->getSettingValue('T:Consumer key')[0],
			'consumer_secret' => $Settings->getSettingValue('T:Consumer secret')[0]
		);

		$this->display_name = $Settings->getSettingValue('T:Display name')[0];
		
	}

	/**
	* Makes empty friends file
	*/

	private function makeFriendsFile() {

		if (!file_exists($this->data_dir))
			mkdir($this->data_dir, 0777, true);

		if (!file_exists($this->twitter_dir))
			mkdir($this->twitter_dir, 0777, true);

		file_put_contents($this->friends_file, json_encode(array(
			'created_at' => time() - REFRESH_TIME,
			'ids' => array()
		)));
	}

	/**
	* Build users I'm following
	*/

	private function buildFriends() {

		$friends_file_contents = file_get_contents($this->friends_file);

		// Parse JSON
		$friends = json_decode($friends_file_contents, true);

		$url = 'https://api.twitter.com/1.1/friends/ids.json';
		$getfield = '?screen_name=' . $this->display_name;
		$requestMethod = 'GET';
		$twitter = new TwitterAPIExchange($this->creds);
		$friends['ids'] = json_decode($twitter->setGetfield($getfield)
					 ->buildOauth($url, $requestMethod)
					 ->performRequest(), true)['ids'];
		$friends['created_at'] = time();

		file_put_contents($this->friends_file, json_encode($friends));
	}

	/**
	* Get users I'm following
	*/

	private function getFriends() {

		if (!file_exists($this->friends_file))
			$this->makeFriendsFile();

		$friends_file_contents = file_get_contents($this->friends_file);

		// Parse JSON
		$friends = json_decode($friends_file_contents, true);

		// Check if due for a data refresh
		if (time() > ($friends['created_at'] + REFRESH_TIME)) {
			$this->buildFriends();
			return $this->getFriends();
		}
		
		return $friends['ids'];
	}

	/**
	* Makes empty user tweets file
	*/

	private function makeUserTweetsFile($user_id) {

		if (!file_exists($this->data_dir))
			mkdir($this->data_dir, 0777, true);

		if (!file_exists($this->twitter_dir))
			mkdir($this->twitter_dir, 0777, true);

		file_put_contents($this->twitter_dir . "/${user_id}-tweets.json", json_encode(array(
			'created_at' => time() - REFRESH_TIME,
			'tweets' => array()
		)));
	}

	/**
	* Build user's tweets
	*/

	private function buildUserTweets($user_id) {

		$user_tweets_file = $this->twitter_dir . "/${user_id}-tweets.json";

		$users_tweets_file_contents = file_get_contents($user_tweets_file);

		// Parse JSON
		$tweets = json_decode($users_tweets_file_contents, true);

		$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
		$getfield = '?user_id=' . $user_id;
		$requestMethod = 'GET';
		$twitter = new TwitterAPIExchange($this->creds);
		$tweets['tweets'] = json_decode($twitter->setGetfield($getfield)
					 ->buildOauth($url, $requestMethod)
					 ->performRequest(), true);
		$tweets['created_at'] = time();

		file_put_contents($user_tweets_file, json_encode($tweets));
	}

	/**
	* Get user's tweets
	*/

	private function getUserTweets($user_id) {

		$user_tweets_file = $this->twitter_dir . "/${user_id}-tweets.json";
		if (!file_exists($user_tweets_file))
			$this->makeUserTweetsFile($user_id);

		$tweets_file_contents = file_get_contents($user_tweets_file);
		// echo $tweets_file_contents;
		// die();

		// Parse JSON
		$tweets = json_decode($tweets_file_contents, true);

		// Check if due for a data refresh
		if (time() > ($tweets['created_at'] + REFRESH_TIME)) {
			$this->buildUserTweets($user_id);
			return $this->getUserTweets($user_id);
		}

		$new_tweets = array();

		foreach ($tweets['tweets'] as $tweet) {
			if (!((isset($tweet[0]["message"]) || $this->checkUserReadTweets($user_id, $tweet['id']))))
				array_push($new_tweets, $tweet);
		}
		
		return $new_tweets;
	}

	/**
	* Makes empty user READ tweets file
	*/

	private function makeUserReadTweetsFile($user_id) {

		if (!file_exists($this->data_dir))
			mkdir($this->data_dir, 0777, true);

		if (!file_exists($this->twitter_dir))
			mkdir($this->twitter_dir, 0777, true);

		file_put_contents($this->twitter_dir . "/${user_id}-read-tweets.json", json_encode(array()));
	}

	/**
	* Get user's READ tweets
	*/

	private function getUserReadTweets($user_id) {

		$user_read_tweets_file = $this->twitter_dir . "/${user_id}-read-tweets.json";

		$users_read_tweets_file_contents = file_get_contents($user_read_tweets_file);

		// Parse JSON
		$ids = json_decode($users_read_tweets_file_contents, true);

		return $ids;
	}

	/**
	* Add to user's READ tweets
	*/

	public function addToUserReadTweets($user_id, $tweet_id) {

		$user_read_tweets_file = $this->twitter_dir . "/${user_id}-read-tweets.json";
		if (!file_exists($user_read_tweets_file))
			$this->makeUserReadTweetsFile($user_id);

		// Parse JSON
		$ids = $this->getUserReadTweets($user_id);

		array_push($ids, $tweet_id);
		
		file_put_contents($this->twitter_dir . "/${user_id}-read-tweets.json", json_encode($ids));
	}

	/**
	* Check for user's READ tweets
	*/

	public function checkUserReadTweets($user_id, $tweet_id) {

		$user_read_tweets_file = $this->twitter_dir . "/${user_id}-read-tweets.json";
		if (!file_exists($user_read_tweets_file))
			$this->makeUserReadTweetsFile($user_id);

		// Parse JSON
		$ids = $this->getUserReadTweets($user_id);

		return in_array($tweet_id, $ids);
	}

	/**
	* Get friends tweets
	*/

	public function getFriendsTweets($soft_limit=25) {

		$tweets = array();

		foreach ($this->getFriends() as $user_id) {
			
			$tweets = array_merge($tweets, $this->getUserTweets($user_id));

			if (count($tweets) > $soft_limit)
				break;

		}

		usort($tweets, array($this, 'sortTweets'));

		return $tweets;

	}

	/**
	* Mark all tweets as read
	*/

	public function markAllTweetsAsRead() {

		foreach ($this->getFriends() as $user_id) {
			
			foreach ($this->getUserTweets($user_id) as $tweet) {
				$this->addToUserReadTweets($tweet['user']['id'], $tweet['id']);
			}

		}

	}

	/**
	* Sort list of tweets by id
	*/

	private function sortTweets($a, $b) {

		return ($a['id'] > $b['id']);

	}

}



/**
 * Twitter-API-PHP : Simple PHP wrapper for the v1.1 API
 *
 * PHP version 5.3.10
 *
 * @category Awesomeness
 * @package  Twitter-API-PHP
 * @author   James Mallison <me@j7mbo.co.uk>
 * @license  MIT License
 * @version  1.0.4
 * @link     http://github.com/j7mbo/twitter-api-php
 */
class TwitterAPIExchange
{
	/**
	 * @var string
	 */
	private $oauth_access_token;
	/**
	 * @var string
	 */
	private $oauth_access_token_secret;
	/**
	 * @var string
	 */
	private $consumer_key;
	/**
	 * @var string
	 */
	private $consumer_secret;
	/**
	 * @var array
	 */
	private $postfields;
	/**
	 * @var string
	 */
	private $getfield;
	/**
	 * @var mixed
	 */
	protected $oauth;
	/**
	 * @var string
	 */
	public $url;
	/**
	 * @var string
	 */
	public $requestMethod;
	/**
	 * The HTTP status code from the previous request
	 *
	 * @var int
	 */
	protected $httpStatusCode;
	/**
	 * Create the API access object. Requires an array of settings::
	 * oauth access token, oauth access token secret, consumer key, consumer secret
	 * These are all available by creating your own application on dev.twitter.com
	 * Requires the cURL library
	 *
	 * @throws \RuntimeException When cURL isn't loaded
	 * @throws \InvalidArgumentException When incomplete settings parameters are provided
	 *
	 * @param array $settings
	 */
	public function __construct(array $settings)
	{
		if (!function_exists('curl_init'))
		{
			throw new RuntimeException('TwitterAPIExchange requires cURL extension to be loaded, see: http://curl.haxx.se/docs/install.html');
		}
		if (!isset($settings['oauth_access_token'])
			|| !isset($settings['oauth_access_token_secret'])
			|| !isset($settings['consumer_key'])
			|| !isset($settings['consumer_secret']))
		{
			throw new InvalidArgumentException('Incomplete settings passed to TwitterAPIExchange');
		}
		$this->oauth_access_token = $settings['oauth_access_token'];
		$this->oauth_access_token_secret = $settings['oauth_access_token_secret'];
		$this->consumer_key = $settings['consumer_key'];
		$this->consumer_secret = $settings['consumer_secret'];
	}
	/**
	 * Set postfields array, example: array('screen_name' => 'J7mbo')
	 *
	 * @param array $array Array of parameters to send to API
	 *
	 * @throws \Exception When you are trying to set both get and post fields
	 *
	 * @return TwitterAPIExchange Instance of self for method chaining
	 */
	public function setPostfields(array $array)
	{
		if (!is_null($this->getGetfield()))
		{
			throw new Exception('You can only choose get OR post fields (post fields include put).');
		}
		if (isset($array['status']) && substr($array['status'], 0, 1) === '@')
		{
			$array['status'] = sprintf("\0%s", $array['status']);
		}
		foreach ($array as $key => &$value)
		{
			if (is_bool($value))
			{
				$value = ($value === true) ? 'true' : 'false';
			}
		}
		$this->postfields = $array;
		// rebuild oAuth
		if (isset($this->oauth['oauth_signature']))
		{
			$this->buildOauth($this->url, $this->requestMethod);
		}
		return $this;
	}
	/**
	 * Set getfield string, example: '?screen_name=J7mbo'
	 *
	 * @param string $string Get key and value pairs as string
	 *
	 * @throws \Exception
	 *
	 * @return \TwitterAPIExchange Instance of self for method chaining
	 */
	public function setGetfield($string)
	{
		if (!is_null($this->getPostfields()))
		{
			throw new Exception('You can only choose get OR post / post fields.');
		}
		$getfields = preg_replace('/^\?/', '', explode('&', $string));
		$params = array();
		foreach ($getfields as $field)
		{
			if ($field !== '')
			{
				list($key, $value) = explode('=', $field);
				$params[$key] = $value;
			}
		}
		$this->getfield = '?' . http_build_query($params, '', '&');
		return $this;
	}
	/**
	 * Get getfield string (simple getter)
	 *
	 * @return string $this->getfields
	 */
	public function getGetfield()
	{
		return $this->getfield;
	}
	/**
	 * Get postfields array (simple getter)
	 *
	 * @return array $this->postfields
	 */
	public function getPostfields()
	{
		return $this->postfields;
	}
	/**
	 * Build the Oauth object using params set in construct and additionals
	 * passed to this method. For v1.1, see: https://dev.twitter.com/docs/api/1.1
	 *
	 * @param string $url           The API url to use. Example: https://api.twitter.com/1.1/search/tweets.json
	 * @param string $requestMethod Either POST or GET
	 *
	 * @throws \Exception
	 *
	 * @return \TwitterAPIExchange Instance of self for method chaining
	 */
	public function buildOauth($url, $requestMethod)
	{
		if (!in_array(strtolower($requestMethod), array('post', 'get', 'put', 'delete')))
		{
			throw new Exception('Request method must be either POST, GET or PUT or DELETE');
		}
		$consumer_key              = $this->consumer_key;
		$consumer_secret           = $this->consumer_secret;
		$oauth_access_token        = $this->oauth_access_token;
		$oauth_access_token_secret = $this->oauth_access_token_secret;
		$oauth = array(
			'oauth_consumer_key' => $consumer_key,
			'oauth_nonce' => time(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_token' => $oauth_access_token,
			'oauth_timestamp' => time(),
			'oauth_version' => '1.0'
		);
		$getfield = $this->getGetfield();
		if (!is_null($getfield))
		{
			$getfields = str_replace('?', '', explode('&', $getfield));
			foreach ($getfields as $g)
			{
				$split = explode('=', $g);
				/** In case a null is passed through **/
				if (isset($split[1]))
				{
					$oauth[$split[0]] = urldecode($split[1]);
				}
			}
		}
		$postfields = $this->getPostfields();
		if (!is_null($postfields)) {
			foreach ($postfields as $key => $value) {
				$oauth[$key] = $value;
			}
		}
		$base_info = $this->buildBaseString($url, $requestMethod, $oauth);
		$composite_key = rawurlencode($consumer_secret) . '&' . rawurlencode($oauth_access_token_secret);
		$oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
		$oauth['oauth_signature'] = $oauth_signature;
		$this->url           = $url;
		$this->requestMethod = $requestMethod;
		$this->oauth         = $oauth;
		return $this;
	}
	/**
	 * Perform the actual data retrieval from the API
	 *
	 * @param boolean $return      If true, returns data. This is left in for backward compatibility reasons
	 * @param array   $curlOptions Additional Curl options for this request
	 *
	 * @throws \Exception
	 *
	 * @return string json If $return param is true, returns json data.
	 */
	public function performRequest($return = true, $curlOptions = array())
	{
		if (!is_bool($return))
		{
			throw new Exception('performRequest parameter must be true or false');
		}
		$header =  array($this->buildAuthorizationHeader($this->oauth), 'Expect:');
		$getfield = $this->getGetfield();
		$postfields = $this->getPostfields();
		if (in_array(strtolower($this->requestMethod), array('put', 'delete')))
		{
			$curlOptions[CURLOPT_CUSTOMREQUEST] = $this->requestMethod;
		}
		$options = $curlOptions + array(
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_HEADER => false,
			CURLOPT_URL => $this->url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 10,
		);
		if (!is_null($postfields))
		{
			$options[CURLOPT_POSTFIELDS] = http_build_query($postfields, '', '&');
		}
		else
		{
			if ($getfield !== '')
			{
				$options[CURLOPT_URL] .= $getfield;
			}
		}
		$feed = curl_init();
		curl_setopt_array($feed, $options);
		$json = curl_exec($feed);
		$this->httpStatusCode = curl_getinfo($feed, CURLINFO_HTTP_CODE);
		if (($error = curl_error($feed)) !== '')
		{
			curl_close($feed);
			throw new \Exception($error);
		}
		curl_close($feed);
		return $json;
	}
	/**
	 * Private method to generate the base string used by cURL
	 *
	 * @param string $baseURI
	 * @param string $method
	 * @param array  $params
	 *
	 * @return string Built base string
	 */
	private function buildBaseString($baseURI, $method, $params)
	{
		$return = array();
		ksort($params);
		foreach($params as $key => $value)
		{
			$return[] = rawurlencode($key) . '=' . rawurlencode($value);
		}
		return $method . "&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $return));
	}
	/**
	 * Private method to generate authorization header used by cURL
	 *
	 * @param array $oauth Array of oauth data generated by buildOauth()
	 *
	 * @return string $return Header used by cURL for request
	 */
	private function buildAuthorizationHeader(array $oauth)
	{
		$return = 'Authorization: OAuth ';
		$values = array();
		foreach($oauth as $key => $value)
		{
			if (in_array($key, array('oauth_consumer_key', 'oauth_nonce', 'oauth_signature',
				'oauth_signature_method', 'oauth_timestamp', 'oauth_token', 'oauth_version'))) {
				$values[] = "$key=\"" . rawurlencode($value) . "\"";
			}
		}
		$return .= implode(', ', $values);
		return $return;
	}
	/**
	 * Helper method to perform our request
	 *
	 * @param string $url
	 * @param string $method
	 * @param string $data
	 * @param array  $curlOptions
	 *
	 * @throws \Exception
	 *
	 * @return string The json response from the server
	 */
	public function request($url, $method = 'get', $data = null, $curlOptions = array())
	{
		if (strtolower($method) === 'get')
		{
			$this->setGetfield($data);
		}
		else
		{
			$this->setPostfields($data);
		}
		return $this->buildOauth($url, $method)->performRequest(true, $curlOptions);
	}
	/**
	 * Get the HTTP status code for the previous request
	 *
	 * @return integer
	 */
	public function getHttpStatusCode()
	{
		return $this->httpStatusCode;
	}
}
?>