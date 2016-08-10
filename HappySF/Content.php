<?php
namespace HappySF;

use Goutte as Goutte;

class Content {
	protected $author = '';
	protected $content = '';

	public function __construct($url) {
		$client = new Goutte\Client();
		$html = $client->request('GET', $url);
		print_r($html->filter('table')->eq(1)->filter('tr:first > td:first > span:first > table:first > tr:first > td:first')->text());
		exit;
		$this->setAuthor($html->filter('span[style=cursor\:hand]')->eq(0)->text());
		$this->setContent($html->filter('td[valign=top]')->eq(1)->text());
	}

	public function setAuthor($author) {
		$this->author = $author;

		return $this;
	}

	public function setContent($content) {
		$this->content = $content;

		return $this;
	}

	public function getAuthor() {
		return $this->author;
	}

	public function getContent() {
		return $this->content;
	}
}
// EOF
