<?php

// VERSION 0.5

// Make sure no one attempts to run this script "directly"
if (!defined('UP')) {
	exit;
}


class DB {
	private $link = FALSE;
	private $linkOpened = FALSE;
	private $query_result;
	private $affected_rows;
	private $lastQuery = '';


	// СОДЕРЖИТ ЭКЗЕМПЛЯР КЛАССА
    private static $instance;


	private function __construct() {
		# Test for missing mysql.so
		# First try to load it
		if (!@/**/extension_loaded('mysqli')) {
			@/**/dl('mysqli.so');
		}

		# Fail now
		# Otherwise we get a suppressed fatal error, which is very hard to track down
		if (!function_exists('mysqli_connect')) {
			throw new Exception("MySQLi functions missing");
		}

		$this->close();
		$this->link = $this->connect();
		$this->linkOpened = (bool)$this->link;
	}

	public function __destruct() {
		$this->close();
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


	public function query_num() {
		$args = func_get_args();
		$sql = call_user_func_array(array($this, 'makeSafeSQL'), $args);
		return $this->makeSafeQuery($sql, FALSE);
	}

	public function query() {
		$args = func_get_args();
		$sql = call_user_func_array(array($this, 'makeSafeSQL'), $args);
		return $this->makeSafeQuery($sql, FALSE);
	}

	public function silentQuery() {
		$args = func_get_args();
		$sql = call_user_func_array(array($this, 'makeSafeSQL'), $args);
		return $this->makeSafeQuery($sql, TRUE);
	}

	/* */
	public function numRows() {
		$args = func_get_args();
		$sql = call_user_func_array(array($this, 'makeSafeSQL'), $args);
		return $this->safeNumRows($sql);
	}

	/* */
	public function getRow() {
		$args = func_get_args();
		$sql = call_user_func_array(array($this, 'makeSafeSQL'), $args);
		return $this->getSafeRow($sql);
	}

	/* */
	public function getData() {
		$args = func_get_args();
		$sql = call_user_func_array(array($this, 'makeSafeSQL'), $args);
		return $this->getSafeData($sql);
	}

	/* */
	public function lastID() {
		return ($this->link) ? mysqli_insert_id($this->link) : FALSE;
	}

	/* */
	public function affected() {
		return ($this->link) ? mysqli_affected_rows($this->link) : FALSE;
	}



	private function makeSafeQuery($sql, $silent=FALSE) {
		if (utf8_strlen($sql) > 140000) {
			throw new Exception('MySQL: Insane query.');
		}

		$this->lastQuery = $sql;
		$this->query_result = mysqli_query($this->link, $sql);

		if (!$this->query_result) {
			if (!$silent) {
				throw new Exception('Ошибка базы данных: «'.$this->lastError().'»');
			}

			return FALSE;
		} else {
			return $this->query_result;
		}
	}


	private function me($str) {
		return is_array($str) ? '' : mysqli_real_escape_string($this->link, $str);
	}


	private function getSafeRow($sql) {
		$result = $this->makeSafeQuery($sql);

		if ($result) {
			@/**/$row = mysqli_fetch_assoc($result);

			if ($this->lastErrno()) {
				throw new Exception('Error in '.__METHOD__.': ' .htmlspecialchars($this->lastError()));
			}

			// free query results
			$this->freeResult();

			return $row;
		} else {
			return FALSE;
		}
	}


	private function getSafeData($sql) {
		$result = $this->makeSafeQuery($sql);

		if ($result) {
			$datas = array();
			while (@/**/$res = mysqli_fetch_assoc($result)) {
				if ($this->lastErrno()) {
					throw new Exception('Error in '.__METHOD__.': ' .htmlspecialchars($this->lastError()));
				}
				$datas[] = $res;
			}
			// free query results
			$this->freeResult();
			return (count($datas) > 0) ? $datas : FALSE;
		} else {
			return FALSE;
		}
	}

	private function safeNumRows($sql) {
		$result = $this->makeSafeQuery($sql);

		if ($result) {
			@/**/$num = mysqli_num_rows($result);

			if ($this->lastErrno()) {
				throw new Exception('Error in '.__METHOD__.': ' .htmlspecialchars($this->lastError()));
			}
			// free query results
			$this->freeResult();
			return $num;
		} else {
			return FALSE;
		}
	}


	private function connect() {
		$link = @/**/mysqli_connect(MYSQL_ADDRESS, MYSQL_LOGIN, MYSQL_PASSWORD, MYSQL_DB);

		if (!$link || mysqli_connect_errno()) {
			throw new Exception('База данных недоступна');
		}

		// Setup the client-server character set (UTF-8)
		if (defined('MYSQL_CHARSET') && !mysqli_query($link, "SET NAMES ".MYSQL_CHARSET)) {
			throw new Exception('Ошибка кодировки в базе данных');
		}

		return $link;
	}

	private function makeSafeSQL() {
		$args = func_get_args();

		$tmpl =& $args[0];
		$tmpl = str_replace('%', '%%', $tmpl);
		$tmpl = str_replace('?', '%s', $tmpl);

		foreach ($args as $i => $v) {
			if (!$i) {
				continue;
			}

			$args[$i] = "'".$this->me($v)."'";
		}

		for ($i=$c=count($args)-1; $i < $c+20; $i++) {
			$args[$i+1] = "UNKNOWN_PLACEHOLDER_$i";
		}

		return call_user_func_array('sprintf', $args);
	}

	private function freeResult() {
		if ($this->query_result) {
			if (!@/**/mysqli_free_result($this->query_result)) {
				//throw new Exception('Невозможно освободить ресурсы в базе данных '.$this->lastQuery);
			}
		}
	}

	private function lastErrno() {
		if ($this->link) {
			return mysqli_errno($this->link);
		} else {
			return mysqli_errno();
		}
	}

	private function lastError() {
		if ($this->link) {
			$error = mysqli_error($this->link);
			/*if (!$error) {
				$error = mysqli_error();
			}*/
		} else {
			$error = mysqli_error();
		}
		return $error;
	}



	private function close() {
		$this->linkOpened = FALSE;

		if ($this->link) {
			return mysqli_close($this->link);
		} else {
			return TRUE;
		}
	}
}

?>
