<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit();
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
		return memcache_add($this->link, $key, serialize($object), 0, $expire);
	}


	public function set($object, $key, $expire) {
		if (!is_numeric($object)) {
			$object = serialize($object);
		}

		return memcache_set($this->link, $key, $object, 0, $expire);
	}


	public function inc($key, $expire) {
		if ($this->get($key)) {
			return memcache_increment($this->link, $key);
		} else {
			return memcache_set($this->link, $key, 1, 0, $expire);
		}
	}

	public function get($key) {
		$object = null;

		if ($key) {
			$object = memcache_get($this->link, $key);
		}

		if (!is_numeric($object) && $object) {
			$object = unserialize($object);
		}

		return $object;
	}

	public function unlink($key) {
		return memcache_delete($this->link, $key);
	}

	public function replace($object, $key) {
		return memcache_replace($this->link, $key, serialize($object));
	}


	public function flush() {
		return memcache_flush($this->link);
	}


	private function connect() {
		if (MEMCACHE_PERSISTENT_CONNECT) {
			$link = memcache_pconnect(MEMCACHE_HOST, MEMCACHE_PORT);
		} else {
			$link = memcache_connect(MEMCACHE_HOST, MEMCACHE_PORT);
		}

		if (!$link) {
			throw new Exception("Memcache: could not connect");
		}

		return $link;
	}
}

?>
