<?php
namespace HappySF;

use Symfony\Component\Yaml\Yaml;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Logger {
	protected $log_file = '';
	protected $log_level = 0;

	public function __construct() {
		$this->initialize();
	}

	public function initialize() {
		$config = Yaml::parse(file_get_contents(BASE_PATH . '/config.yaml'));

		$this->log_file = $config['log_file'];
		$this->log_level = '';
	}
}
// EOF
