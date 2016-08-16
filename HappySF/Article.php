<?php
namespace HappySF;

use Sunra\PhpSimple\HtmlDomParser;
use GuzzleHttp as GuzzleHttp;

class Article {
	protected $url = '';
	protected $title = '';
	protected $author = '';
	protected $date = '';
	protected $content = '';

	public function __construct($element) {
		// url, title
		$anchor = $element->find('a', 0);
		$this->setURL($anchor->href);
		$this->setTitle($anchor->innertext);

		// date
		$date = $element->find('span[title]', 0)->title;
		$date = preg_replace('/([^0-9])/', '', $date);
		$this->setDate(strtotime($date));

		// author, content
		$client = new GuzzleHttp\Client();
		$result = $client->request('GET', $this->getURL());

		if($result->getStatusCode() !== 200) {
			// TODO logging
		}

		$html = HtmlDomParser::str_get_html($result->getBody());
		$this->setAuthor($html->find('span[style=cursor:hand]', 0)->innertext);
		$this->setContent($html->find('td[valign=top]', 1)->innertext);
	}

	public function setURL($url) {
		$this->url = 'http://happysf.net/zeroboard/' . $url;

		return $this;
	}

	private function convertCharacterEncodingToUTF8($text) {
		return (empty($text) ? $text : iconv('EUC-KR', 'UTF-8//IGNORE', $text));
	}

	public function setTitle($title) {
		$this->title = $this->convertCharacterEncodingToUTF8($title);

		return $this;
	}

	public function setDate($date) {
		$this->date = $date;

		return $this;
	}

	public function setContent($content) {
		$this->content = $this->convertCharacterEncodingToUTF8($content);

		return $this;
	}

	public function setAuthor($author) {
		$this->author = $this->convertCharacterEncodingToUTF8($author);
	}

	public function getURL() {
		return $this->url;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getDate() {
		return $this->date;
	}

	public function getAuthor() {
		return $this->author;
	}

	public function getContent() {
		return $this->content;
	}

	public function toString() {
		return 'author: ' . $this->getAuthor() . ', ' .
			'url: ' . $this->getURL() . ', ' .
			'title: ' . $this->getTitle() . ', ' .
			'date: ' . $this->getDate();
	}
}
// EOF
