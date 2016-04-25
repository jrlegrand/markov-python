<?php
require('simple_html_dom.php');
require 'markov.php';
require 'twitteroauth/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;

// set keys
$consumerKey = '';
$consumerSecret = '';
$accessToken = '';
$accessTokenSecret = '';

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
	
	$markov = substr($markov, strpos($markov, ' '));
	
	$markov = substr($markov, 0, strrpos($markov, ' '));
	
	$final_markov = ucfirst($markov);
	
	if (strlen($markov) < 140)
	{
		$punctuation = array('!', '.', ',', ':', ';', '(', ')', '?');
		if (!in_array(substr($markov, -1), $punctuation)) $markov .= '.';
	}
	
	$result['tweet'] = $final_markov;
	
	return $result;
}

$result = get_markov_python_tweet();

$tweet = $result['tweet'];

$status = $connection->post("statuses/update", array("status" => $tweet));


?>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

    <title>Markov Python Tweet Generator</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

<body>
	<div class="container" style="padding-top: 50px;">
		<div class="jumbotron">
			<h1><?php echo $status->text; ?></h1>
			<p><a href="https://twitter.com/MarkovPython/status/<?php echo $status->id; ?>" target="_blank">View on Twitter</a></p>
			<p><a href="http://topular.in/cron-folder/markov/tweet.php" class="btn btn-primary btn-lg">Tweet again!</a></p>
		</div>
		<p class="lead">From Monty Python's Flying Circus episode <?php echo $result['episode']; ?></p>
		<p><?php echo $result['text']; ?></p>
	</div>
</body>
