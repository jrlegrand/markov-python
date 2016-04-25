<?php
require('simple_html_dom.php');
require 'markov.php';
require 'twitteroauth/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;

// set keys
$consumerKey = 'LY158cnwOQDfrknIthcvlFZFZ';
$consumerSecret = 'P9ff3vOqwqMDrbQQg8beukKrG9Qk9nXnkYtI4RESkAij0QMq18';
$accessToken = '4570877601-XQDipvlfiVzsehpznnGA7eD4wjfrx9jjvNIywfC';
$accessTokenSecret = 'GyYuGTiQjvTNdJV6DqXWumVTPEPlsfliBuljOTL3nKE4d';

// create connection
$connection = new TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);

function get_markov_python_tweet() {
	// pick a random episode
	$episode = str_pad(rand(1,45), 2, '0', STR_PAD_LEFT);
	$result['episode'] = $episode;

	// get HTML from Monty Python website
	$html = file_get_html('http://www.ibras.dk/montypython/episode' . $episode . '.htm');
	$result['html'] = $html;

	// extract text from HTML
	$text = '';
	
	foreach ($html->find('td font') as $t)
	{
		$text .= $t->plaintext . ' ';
	}
	$result['text'] = $text;
	
	// generate text with markov library
	$order = 4;
	$result['order'] = $order;
	$length = 140;
	$result['length'] = $length;

	$markov_table = generate_markov_table($text, $order);
	$markov = generate_markov_text($length, $markov_table, $order);

	if (get_magic_quotes_gpc()) $markov = stripslashes($markov);
	
	$result['tweet'] = $markov;
	
	return $result;
}

$result = get_markov_python_tweet();

$tweet = $result['tweet'];

$status = $connection->post("statuses/update", array("status" => $tweet));

print_r($status);