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

// config function
function getConfig() {
	static $config = NULL;

	if(is_null($config)) {
		$config = Yaml::parse(file_get_contents(BASE_PATH . '/config.yaml'));
	}

	return $config;
}

// output
$output = new HappySF\Output;

try {
	// use cached data
	$output->cacheData('rss', function() {
		// config
		$config = getConfig();

		// http request
		$client = new GuzzleHttp\Client();
		$result = $client->request('GET', 'http://happysf.net/zeroboard/zboard.php?id=reader');

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

		return $feed->export($config['feed']['type']);
	});
} catch(GuzzleHttp\Exception\ClientException $e) {
	$output
		->unsetCacheHeader()
		->setContentType('text/html; charset=utf-8')
		->setStatusCode($e->getResponse()->getStatusCode())
		->write($e->getMessage());
} catch(Exception $e) {
	$output
		->unsetCacheHeader()
		->setContentType('text/html; charset=utf-8')
		->setStatusCode(500)
		->write('Message: ' . $e->getMessage() . '<br />Stacks:<br />' . str_replace("\n", '<br />', $e->getTraceAsString()));
}

// flush
$output->flush();
// EOF
