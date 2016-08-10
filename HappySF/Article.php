<?php
namespace HappySF;

use Symfony\Component as SymfonyComponent;

class Article {
	protected $articleId = 0;
	protected $url = '';
	protected $title = '';
	protected $content = NULL;

	public function __construct(SymfonyComponent\DomCrawler\Crawler $element) {
		$children = $element->children();

		$this->setArticleId((int) $children->eq(0)->text());

		$anchor = $children->eq(1)->filter('a');
		$this->setURL($anchor->attr('href'));
		$this->setTitle($anchor->text());
	}

	public function loadContent($url) {
		$this->setContent(new Content($url));

		return $this;
	}

	public function setArticleId($articleId) {
		$this->articleId = $articleId;

		return $this;
	}

	public function setURL($url) {
		$this->url = $url;

		return $this;
	}

	public function setTitle($title) {
		$this->title = $title;

		return $this;
	}

	public function setDate($date) {
		$this->date = $date;

		return $this;
	}

	public function setContent(Content $content) {
		$this->content = $content;

		return $this;
	}

	public function getArticleId() {
		return $this->articleId;
	}

	public function getURL() {
		return $this->url;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getContent() {
		return $this->content;
	}

	public function toString() {
		return 'article_id: ' . $this->getArticleId() . ', ' .
			'url: ' . $this->getURL() . ', ' .
			'title: ' . $this->getTitle();
	}
}
// EOF
