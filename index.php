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
$client = new Goutte\Client();
$html = $client->request('GET', 'http://happysf.net/zeroboard/zboard.php?id=reader');

foreach($html->filter('tr[bgcolor=ffffff]') as $element) {
	$article = new HappySF\Article(new SymfonyComponent\DomCrawler\Crawler($element));
}
// EOF
