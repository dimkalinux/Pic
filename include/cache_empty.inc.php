<?php

// Make sure no one attempts to run this script "directly"
if (!defined('UP')) {
	exit;
}

class Cache {
	private $link;
	// Содержит экземпляр класса
    private static $instance;

	private function __construct() {
		$this->link = $this->connect();
	}

	public function __destruct() {
		//$this->close();
	}

	// Метод синглтон
    public static function singleton() {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }

    // Предотвращает клонирование экземпляра класса
    public function __clone() {
        trigger_error('Клонирование запрещено.', E_USER_ERROR);
    }


	public function add($object, $key, $expire) {
		return true;
	}

	public function set($object, $key, $expire) {
		return true;
	}


	public function inc($key, $expire) {

	}

	public function get($key) {
		return null;
	}

	public function unlink($key) {
		return true;
	}

	public function replace($object, $key) {
		return true;
	}

	public function flush() {
		return true;
	}

	public function clearStat() {
		return;
	}

	private function connect() {
		return true;
	}
}

?>
