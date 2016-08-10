<?php
namespace HappySF;

/**
 * URLFormatter
 *
 * 주어진 URL 요소를 바탕으로 URL을 생성해주는 클래스.
 * 단, 본 Formatter는 rfc 스펙을 충실히 반영하지 않았으므로, 코드를 충분히 이해한 뒤에
 * 제한된 곳에서만 사용해야 함.
 */
class URLFormatter {
	protected $schema = 'http';
	protected $user = NULL;
	protected $password = NULL;
	protected $domain = NULL;
	protected $port = NULL;
	protected $path = NULL;
	protected $query_string = array();
	protected $fragment = NULL;

	/**
	 * getClone
	 *
	 * cloning된 객체를 반환
	 *
	 * @access	public
	 * @return	object
	 */
	public function getClone() {
		return clone $this;
	}

	/**
	 * setSchema
	 *
	 * @access	public
	 * @param	string		$schema			schema
	 * @return	void
	 */
	public function setSchema($schema) {
		$this->schema = $schema;

		return $this;
	}

	/**
	 * setUser
	 *
	 * @access	public
	 * @param	string		$user			user
	 * @return	void
	 */
	public function setUser($user) {
		$this->user = $user;

		return $this;
	}

	/**
	 * setPassword
	 *
	 * @access	public
	 * @param	string		$password		password
	 * @return	void
	 */
	public function setPassword($password) {
		$this->password = $password;

		return $this;
	}

	/**
	 * getUserAndPassword
	 *
	 * @access	public
	 * @return	string
	 */
	public function getUserAndPassword() {
		$result = '';

		if(!is_null($this->user)) {
			$result .= $this->user;
		}

		if(!is_null($this->password)) {
			$result .= ':' . $this->password;
		}

		return !empty($result) ? $result . '@' : '';
	}

	/**
	 * setDomain
	 *
	 * @access	public
	 * @param	string		$domain			domain
	 * @return	void
	 */
	public function setDomain($domain) {
		$this->domain = $domain;

		return $this;
	}

	/**
	 * setPort
	 *
	 * @access	public
	 * @param	string		$port			port
	 * @return	void
	 */
	public function setPort($port) {
		if(!is_null($port)) {
			$this->port = (int) $port;
		}

		return $this;
	}

	/**
	 * getDomainAndPort
	 *
	 * @access	public
	 * @return	string
	 */
	public function getDomainAndPort() {
		return $this->domain . (!is_null($this->port) ? ':' . $this->port : '');
	}

	/**
	 * setPath
	 *
	 * @access	public
	 * @param	string		$path			path
	 * @return	void
	 */
	public function setPath($path) {
		$this->path = '/' . ltrim($path, '/');

		return $this;
	}

	/**
	 * getPath
	 *
	 * @access	public
	 * @return	string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * setQueryString
	 *
	 * @access	public
	 * @param	mixed		$query_string	query string
	 * @return	void
	 */
	public function setQueryString($query_string) {
		if(is_string($query_string)) {
			parse_str($query_string, $output);
			$query_string = $output;
		}

		$this->query_string = $query_string;

		return $this;
	}

	/**
	 * appendQueryString
	 *
	 * @access	public
	 * @param	mixed		$query_string	query string
	 * @return	object
	 */
	public function appendQueryString($query_string) {
		if(is_string($query_string)) {
			parse_str($query_string, $output);
			$query_string = $output;
		}

		$this->query_string = array_merge($this->query_string, $query_string);

		return $this;
	}

	/**
	 * setFragment
	 *
	 * @access	public
	 * @param	string		$fragment		fragment
	 * @return	void
	 */
	public function setFragment($fragment) {
		$this->fragment = $fragment;

		return $this;
	}

	/**
	 * getURL
	 *
	 * @access	public
	 * @return	string
	 */
	public function getURL() {
		return
			$this->schema . '://' . // schema
			$this->getUserAndPassword() . // user and password
			$this->getDomainAndPort() . // domain and port
			$this->path . // path
			(is_array($this->query_string) && count($this->query_string) ? '?' . http_build_query($this->query_string) : '') . // query string
			(!is_null($this->fragment) ? '#' . $this->fragment : ''); // fragment

	}
}
// EOF
