<?php
// base_path
define('BASE_PATH', dirname(__FILE__));

// autoloader
require_once BASE_PATH . '/vendor/autoload.php';

use Symfony\Component as SymfonyComponent;
use Goutte as Goutte;
use HappySF as HappySF;

// get config from yaml
$config = SymfonyComponent\Yaml\Yaml::parse(file_get_contents(BASE_PATH . '/config.yaml'));

// url container
$url = new Happysf\URL($config['domain']);

// crawler
$client = new Goutte\Client();
$html = $client->request('GET', $url->getBoardURL($config['board_id']));

foreach($html->filter('tr[bgcolor=ffffff]') as $element) {
	$article = new HappySF\Article(new SymfonyComponent\DomCrawler\Crawler($element));
	$article->loadContent($url->getArticleURL($config['board_id'], $article->getArticleId()));

	echo $article->getContent()->getContent();
	exit;
}
// EOF
