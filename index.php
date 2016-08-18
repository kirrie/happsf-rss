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
			->setFeedLink($config['feed']['feed_url'], $config['feed']['type'])
			->addAuthor($config['feed']['author']) // array type
			->setDescription($config['feed']['description']);

		// lastModified
		$lastModified = NULL;

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
					->setDateModified($article->getDate())
					->setDateCreated($article->getDate())
					->setDescription($article->getContent())
					->setContent($article->getContent())
			);

			// a most recent article's pubdate is last modified date of this feed.
			if(is_null($lastModified)) {
				$lastModified = $article->getDate();
			}
		}

		$feed->setDateModified($lastModified);

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
