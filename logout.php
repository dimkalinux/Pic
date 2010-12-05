<?

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';

$async = FALSE;
if (isset($_GET['async'])) {
    $async = TRUE;
    unset($_GET['async']);
}

try {
    $o_ami_user = new AMI_User();
  	$o_ami_user->logout();

    ami_redirect(ami_link('root'));
}  catch (AppLevelException $e) {
    if ($async) {
	ami_async_response(array('error'=> 1, 'message' => $e->getMessage()), AMI_ASYNC_JSON);
    } else {
	ami_show_error_message($e->getMessage());
    }
} catch (Exception $e) {
    if ($async) {
	ami_async_response(array('error'=> 1, 'message' => $e->getMessage()), AMI_ASYNC_JSON);
    } else {
	ami_show_error($e->getMessage());
    }
}




?>
