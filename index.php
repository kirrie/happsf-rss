<?php
// base_path
define('BASE_PATH', dirname(__FILE__));

// autoloader
require_once BASE_PATH . '/vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Sunra\PhpSimple\HtmlDomParser;
use GuzzleHttp as GuzzleHttp;
use HappySF as HappySF;
use Zend as Zend;

// response
$response = new GuzzleHttp\Psr7\Response;
$response = $response
	->withStatus(200)
	->withHeader('X-Cache', 'MISS from local')
	->withBody(new GuzzleHttp\Psr7\Stream(fopen('php://memory', 'r+')));
$body = $response
	->getBody();

try {
	// config
	$config = Yaml::parse(file_get_contents(BASE_PATH . '/config.yaml'));

	// use cached data
	$cache_key = 'rss';
	$cache = Zend\Cache\StorageFactory::factory(array(
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
	$cached_data = $cache->getItem($cache_key, $cache_hit);
	if($cache_hit) {
		$response = $response
			->withHeader('Content-Type', 'application/rss+xml')
			->withHeader('X-Cache', 'HIT from local');
		$body
			->write($cached_data);
	}

	// http request
	$client = new GuzzleHttp\Client();
	$result = $client->request('GET', 'http://happysf.net/zeroboard/zboard1.php?id=reader');

	// rss feed channel
	$feed = new Zend\Feed\Writer\Feed;
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
	$response = $response
		->withHeader('Content-Type', 'application/rss+xml');
	$body
		->write($rss);

	// cache it
	$cache->setItem($cache_key, $rss);
} catch(GuzzleHttp\Exception\ClientException $e) {
	$response = $response
		->withStatus($e->getResponse()->getStatusCode());
	$body
		->write($e->getMessage());
}

// flush
$headers = $response->getHeaders();
$body = $response->getBody();

if(!headers_sent()) {
	// header part
	header(sprintf(
		'HTTP/%s %s %s',
		$response->getProtocolVersion(),
		$response->getStatusCode(),
		$response->getReasonPhrase()
	));

	foreach($headers as $header_name => $header_values) {
		foreach($header_values as $header_value) {
			header(sprintf('%s: %s', $header_name, $header_value), FALSE);
		}
	}
}

// body part
if($body->getSize() !== 0) {
	if($body->isSeekable()) {
		$body->rewind();
	}

	while(!$body->eof()) {
		echo $body->read(4096);

		if(connection_status() != CONNECTION_NORMAL) {
			break;
		}
	}
}
// EOF
