<?

// Make sure no one attempts to run this script "directly"
if (!defined('UP')) {
	exit;
}

if (!defined('UP_ROOT')) {
	define('UP_ROOT', '../');
}

class AJAX extends Common {
	public function updateService() {
		global $out, $result;

		try {
			$serviceID = isset($_GET['t_id']) ? $_GET['t_id'] : FALSE;
			if (!$serviceID) {
				throw new Exception('Отсутствует аргумент ID');
			}

			$service = new Service(TRUE);
			if (!method_exists($service, $serviceID)) {
				throw new Exception('Unknown method '.$serviceID);
			}

			$out = $service->$serviceID();
			$result = 1;
		} catch (Exception $e) {
			$this->exitWithError($e->getMessage());
		}
	}
}

?>
