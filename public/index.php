<?php 
	session_set_cookie_params(8*60*60);
	ini_set("session.save_handler","files");
	ini_set("session.hash_bits_per_character","4");
	ini_set("session.gc_probability","1");
	ini_set("session.gc_divisor","100");
	ini_set("session.bug_compat_42","On");
	ini_set("session.cookie_httponly","");
	ini_set('session.gc_maxlifetime', '28800'); 

	session_start();	
	$container = isset($_REQUEST['tab'])? $_REQUEST['tab']:'content';
	ob_start();
	error_reporting ( E_ALL  );
	defined('APPLICATION_PATH')
	|| define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

	// Define application environment
	defined('APPLICATION_ENV')
		|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

	// Ensure library/ is on include_path
	set_include_path(implode(PATH_SEPARATOR, array(
		realpath(APPLICATION_PATH . '/../library'),
		get_include_path(),
	)));

	
	/** Zend_Application */
	require_once 'Zend/Application.php';  

	// Create application, bootstrap, and run
	$application = new Zend_Application(APPLICATION_ENV,APPLICATION_PATH . '/configs/application.php');
	

	if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'AJAX')
	{
		//print_r($_SERVER);
		
	}

	$type = isset($_REQUEST['type'])?$_REQUEST['type']:'';
	$location = isset($_REQUEST['location'])?$_REQUEST['location']:'';
	$_SESSION['type'] = $type;
	$_SESSION['location'] = $location;
	/*
	if(!empty($location))
	{
		# Define the application path
		//if(!defined('BASE_URL')) define('BASE_URL', 'http://infaktura/'.$location.'/');
		if(!defined('BASE_URL')) define('BASE_URL', 'http://infaktura.no/'.$location.'/');
	}
	else
	{
		# Define the application path
		if(!defined('BASE_URL')) define('BASE_URL', 'http://infaktura/');
		# if(!defined('BASE_URL')) define('BASE_URL', 'http://infaktura.no/');
	}
	*/
	//if(!defined('BASE_URL')) define('BASE_URL', 'http://infaktura/');
	//if(!defined('BASE_DIR')) define('BASE_DIR', 'E:/wamp/www/infaktura/');
	 
	$base_url = 'http://infaktura/';
	$base_dir = 'F:/wamp/www/infaktura/';
	$base_url_secure = "http://infaktura/";
	if(isset($_SERVER['HTTP_HOST']))
	{
		if(strpos($_SERVER['HTTP_HOST'], 'www.') === false)
		{
			$base_url = 'http://infaktura/';
			$base_url_secure = "http://infaktura/";
			$base_dir = 'F:/wamp/www/infaktura/';
		}
	}
	//if(!defined('BASE_URL')) define('BASE_URL', $base_url_secure);
	if(!defined('BASE_URL')) define('BASE_URL', $base_url);
 	if(!defined('BASE_DIR')) define('BASE_DIR', $base_dir);
 	//if(!defined('BASE_URL_SECURE')) define('BASE_URL_SECURE', $base_url_secure);
	if(!defined('BASE_URL_SECURE')) define('BASE_URL_SECURE', $base_url);
 	 
	if(!defined('CREMUL_EMAIL')) define('CREMUL_EMAIL', 'contact2arunsingh@gmail.com');
	#bjorn@egoria.no

	if(!defined('INVOICE_PDF_APP_DIR')) define('INVOICE_PDF_APP_DIR', BASE_DIR.'public/pdf_files/invoices/');
	if(!defined('INVOICE_PDF_APP_URL')) define('INVOICE_PDF_APP_URL', BASE_URL.'public/pdf_files/invoices/');
	if(!defined('REMINDER_PDF_APP_DIR')) define('REMINDER_PDF_APP_DIR', BASE_DIR.'public/pdf_files/reminders/');
	if(!defined('REMINDER_PDF_APP_URL')) define('REMINDER_PDF_APP_URL', BASE_URL.'public/pdf_files/reminders/');
	if(!defined('MEMOS_PDF_APP_DIR')) define('MEMOS_PDF_APP_DIR', BASE_DIR.'public/pdf_files/credit_memos/');
	if(!defined('MEMOS_PDF_APP_URL')) define('MEMOS_PDF_APP_URL', BASE_URL.'public/pdf_files/credit_memos/');
	if(!defined('REPORT_PDF_APP_URL')) define('REPORT_PDF_APP_URL',BASE_URL.'public/pdf_files/reports/balance/');
	if(!defined('REPORT_PDF_APP_DIR')) define('REPORT_PDF_APP_DIR',BASE_DIR.'public/pdf_files/reports/balance/');
	if(!defined('REPORT_APP_URL')) define('REPORT_APP_URL',BASE_URL.'public/pdf_files/reports/');
	if(!defined('REPORT_APP_DIR')) define('REPORT_APP_DIR',BASE_DIR.'public/pdf_files/reports/');
	if(!defined('ESTIMATE_APP_URL')) define('ESTIMATE_APP_URL',BASE_URL.'public/pdf_files/estimate/');
	if(!defined('ESTIMATE_APP_DIR')) define('ESTIMATE_APP_DIR',BASE_DIR.'public/pdf_files/estimate/');

	if(!defined('IMAGE_WIDTH')) define('IMAGE_WIDTH', '260');
	if(!defined('IMAGE_HEIGHT')) define('IMAGE_HEIGHT', '60');
	if(!defined('IMAGE_SIZE')) define('IMAGE_SIZE', '2'); # Put in MB
	
	if(!defined('LOGO_WIDTH')) define('LOGO_WIDTH', '4');

	if(!defined('RECEIVER_MAIL')) define('RECEIVER_MAIL', 'pankaj.dadure@gmail.com'); #bjorn@egoria.no
	
	 
	define('DEBUG','1');
	# Admin username
	define('USERNAME','televakt_admin7');  
	# Admin password
	define('PASSWORD','bjorn_televakttest7');//bjorn_televakt
	# full path of script which will execute request (API script path)
	define('API_URL', 'http://sms.televakt.no/api/controller.php'); 
	define('REFILL_URL', 'http://sms.televakt.no/api/refill.php'); 
	define('FETCHER_URL', 'http://sms.televakt.no/api/fetcher.php'); 

	# Path of php executable
	define('PHP_EXECUTABLE', '/hsphere/shared/php5/bin/php-cli');
	
	foreach($_REQUEST as $key=>$value)
	{
		if(!is_array($value))
		{
			$_REQUEST[$key] = htmlentities(stripslashes(urldecode($value)), ENT_QUOTES);
		}
		else
		{
			foreach($value as $k=>$v)
			{
				if(!is_array($v))
				{
					$_REQUEST[$key][$k] = htmlentities(stripslashes(urldecode($v)), ENT_QUOTES);
				}
			}
		}
	}

	$path = $type."/";
	if(!empty($location))
	{
		$pos = strpos($location, "/");
		if($pos == true)
			 $location = substr($location, 0, $pos);
		 $path .= $location."/";

		if($type == 'customer')
		{
			$path = $location."/".$type."/";
		}
	}
	
	if(!defined('APPLICATION_URL')) define('APPLICATION_URL', BASE_URL.$path);
	
	if(empty($type))
	{
		die('wrong url. Pease type correct url.');
	}
	$ctrl  = Zend_Controller_Front::getInstance();
	//$ctrl->setParam('noViewRenderer', true); 
	$router = $ctrl->getRouter();

	if($type == 'admin')
	{
	# Default for admin
	$route = new Zend_Controller_Router_Route(
			'admin/:controller/:action/*',
			array(
				'type'		=> 'admin',
				'controller' => 'index',
				'action'     => 'index'
			)
		);
	}

	if($type == 'partner')
	{
		# Default for partner
		$route = new Zend_Controller_Router_Route(
			'partner/:controller/:action/*',
			array(
				'type'		=> 'partner',
				'controller' => 'index',
				'action'     => 'index'
			)
		);
	}	

	if($type == 'customer')
	{
		# Default for partner
		$route = new Zend_Controller_Router_Route(
			'customer/:controller/:action/*',
			array(
				'type'		=> 'customer',
				'controller' => 'index',
				'action'     => 'index'
			)
		);
	}

	if($type == 'customer'  && !empty($location))
	{
		# Default for partner
		$route = new Zend_Controller_Router_Route(
			':location/:type/:controller/:action/*',
			array(
				'type'		=> 'customer',
				'controller' => 'index',
				'action'     => 'index'
			)
		);
	}


	$router->addRoute('default', $route);
	
	$ctrl->setBaseUrl('/');
	
	
	
	if(!defined('_CRONJOB_') || _CRONJOB_ == false)
	{
	
		$application->bootstrap()
				->run();
	}

	$body = ob_get_contents();
	ob_end_clean();

	if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'AJAX')
	{
		$body = !empty($body)?encode_text($body):'';
		/**
		** If destination info is present return the response by attaching it.
		** javascript will paste the content in given div.
		**/
		if(!empty($container))
		{
			echo "show_page*".$container."^".$body;
		}
		else
		{
			# Returns the result without destination div. The default div 'content; will be used.
			echo "show_page*".$body;
		}
	}
	else
	{
		# For page load
		echo $body;	
	}

	