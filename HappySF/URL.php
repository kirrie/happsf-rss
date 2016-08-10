<?php
namespace HappySF;

class URL {
	protected $url = NULL;

	public function __construct($domain) {
		$this->url = new URLFormatter;
		$this->url->setDomain($domain);
	}

	public function getBoardURL($boardId) {
		return $this->url->getClone()->setPath('zeroboard/zboard.php')->setQueryString(array('id' => $boardId))->getURL();
	}

	public function getArticleURL($boardId, $articleId) {
		return $this->url->getClone()->setPath('zeroboard/zboard.php')->setQueryString(array('id' => $boardId, 'no' => $articleId))->getURL();
	}
}
// EOF
