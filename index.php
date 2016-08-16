<?php
// base_path
define('BASE_PATH', dirname(__FILE__));

// autoloader
require_once BASE_PATH . '/vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Sunra\PhpSimple\HtmlDomParser;
use GuzzleHttp as GuzzleHttp;
use HappySF as HappySF;
use Zend\Feed;
use Zend\Cache;

// config
$config = Yaml::parse(file_get_contents(BASE_PATH . '/config.yaml'));

// output function
function output($rss, $callback = NULL) {
	if(!headers_sent()) {
		header('Content-type: application/rss+xml');
	}
	echo $rss;

	if(!is_null($callback) && is_callable($callback)) {
		$callback();
	}

	exit;
}

// cache
$cache_key = 'rss';
$cache = Cache\StorageFactory::factory(array(
	'adapter' => array(
		'name' => 'filesystem',
		'ttl' => $config['cache']['ttl'],
		'cache_dir' => BASE_PATH . '/cache'
	),
	'plugins' => array(
		'exception_handler' => array(
			'throw_exceptions' => FALSE
		)
	)
));
$cache_result = $cache->getItem($cache_key, $cache_success);
if($cache_success) {
	output($cache_result);
}

// http request
$client = new GuzzleHttp\Client();
$result = $client->request('GET', 'http://happysf.net/zeroboard/zboard.php?id=reader');

// abnormal response
if($result->getStatusCode() !== 200) {
	// TODO logging
}

// rss feed channel
$feed = new Feed\Writer\Feed;
$feed
	->setTitle($config['feed']['title'])
	->setLink($config['feed']['homepage_url'])
	->setDescription($config['feed']['description'])
	->setFeedLink($config['feed']['feed_url'], $config['feed']['type'])
	->setDateModified(time())
	->addAuthor($config['feed']['author']);

$html = HtmlDomParser::str_get_html($result->getBody());
foreach($html->find('tr[bgcolor=ffffff]') as $element) {
	$article = new HappySF\Article($element);
	$feed->addEntry(
		$feed->createEntry()
			->setTitle($article->getTitle())
			->setLink($article->getURL())
			->addAuthor(array(
				'name' => $article->getAuthor()
			))
			->setDateCreated($article->getDate())
			->setDateModified($article->getDate())
			->setContent($article->getContent())
	);
}

$rss = $feed->export($config['feed']['type']);
output($rss, function() use($rss, $cache, $cache_key) {
	$cache->setItem($cache_key, $rss);
});
// EOF
