<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
    exit();
}


class Logger {
    private $db;
    // Содержит экземпляр класса
    private static $instance;


    public function __construct() {
	$this->db = DB::singleton();
    }

    public function __destruct() {
	    // ???
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

    public function info($message) {
	$this->log('info', $message);
    }

    public function debug($message) {
	if (AMI_DEBUG === TRUE) {
	    $this->log('debug', $message);
	}
    }

    public function warn($message) {
	$this->log('warn', $message);
    }

    public function error($message) {
	$this->log('error', $message);
    }


    private function log($type, $message) {
	if (!$this->db) {
	    return;
	}

	if (!empty($message)) {
	    $message = utf8_substr($message, 0, 2048);
	    $this->db->silentQuery("INSERT INTO `logs` VALUES('', NOW(), '$type', ?)", $message);
	}
    }
}

?>
