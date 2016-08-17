<?php
namespace HappySF;

use Symfony\Component\Yaml\Yaml;
use GuzzleHttp\Psr7;
use Zend\Cache;

class Output {
	protected $response = NULL;

	public function __construct() {
		$this->response = new Psr7\Response;
		$this->response = $this->response->withBody(new Psr7\Stream(fopen('php://memory', 'r+')));
	}

	public function setStatusCode($statusCode) {
		$this->response = $this->response->withStatus($statusCode);

		return $this;
	}

	public function setContentType($contentType) {
		$this->response = $this->response->withHeader('Content-Type', $contentType);

		return $this;
	}

	public function setContentLength($contentLength) {
		$this->response = $this->response->withHeader('Content-Length', $contentLength);

		return $this;
	}

	public function setCacheHit($from = 'local') {
		$this->response = $this->response->withHeader('X-Cache', 'HIT from ' . $from);

		return $this;
	}

	public function setCacheMiss($from = 'local') {
		$this->response = $this->response->withHeader('X-Cache', 'Miss from ' . $from);

		return $this;
	}

	public function unsetCacheHeader() {
		$this->response = $this->response->withoutHeader('X-Cache');

		return $this;
	}

	public function write($text) {
		$this->response->getBody()->write($text);
	}

	public function cacheData($cacheKey, \Closure $callback) {
		// for cache config
		$config = getConfig();

		// cache
		$cache = Cache\StorageFactory::factory(array(
			'adapter' => array(
				'name' => 'filesystem',
				'options' => array(
					'ttl' => $config['cache']['ttl'],
					'cache_dir' => BASE_PATH . '/cache'
				)
			),
			'plugins' => array(
				'exception_handler' => array(
					'throw_exceptions' => FALSE
				)
			)
		));
		$data = $cache->getItem($cacheKey, $isCacheHit);
		$this
			->setContentType('application/rss+xml; charset=utf-8')
			->setCacheHit();

		if(!$isCacheHit) {
			$data = $callback();
			$cache->setItem($cacheKey, $data);
			$this->setCacheMiss();
		}

		$this->write($data);
	}

	public function flush() {
		// content-length
		$body = $this->response->getBody();
		$this->setContentLength($body->getSize());

		if(!headers_sent()) {
			// header part
			header(sprintf(
				'HTTP/%s %s %s',
				$this->response->getProtocolVersion(),
				$this->response->getStatusCode(),
				$this->response->getReasonPhrase()
			));

			foreach($this->response->getHeaders() as $headerName => $headerValues) {
				foreach($headerValues as $headerValue) {
					header(sprintf('%s: %s', $headerName, $headerValue), FALSE);
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
	}
}
// EOF
